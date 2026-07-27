<?php

namespace App\Support;

/**
 * مساعد مركزي لإدارة صلاحيات لوحة التحكم.
 *
 * يقرأ التعريفات من config/permissions.php ويبني/يتحقق من مصفوفات الصلاحيات
 * المخزّنة في users.permissions كـ JSON.
 */
class AdminPermissions
{
    /**
     * يُرجع مخطط الصلاحيات الكامل من ملف الإعدادات.
     *
     * كيف يعمل: يقرأ config('permissions.scopes') الذي يحتوي كل نطاق (مثل categories)
     * مع تسمياته وإجراءاته المسموحة.
     *
     * لماذا: واجهة تعديل الصلاحيات والـ seeders تحتاج مصدراً واحداً للحقيقة
     * بدلاً من تكرار المصفوفات في أكثر من ملف.
     */
    public static function schema(): array
    {
        return config('permissions.scopes', []);
    }

    /**
     * يُرجع أسماء القوالب الجاهزة (مثل full_admin، read_only).
     *
     * كيف يعمل: يقرأ مفاتيح مصفوفة permissions.templates من الإعدادات.
     *
     * لماذا: عند دعوة مدير جديد أو في الـ seeder نختار قالباً جاهزاً
     * بدلاً من بناء الصلاحيات يدوياً في كل مرة.
     */
    public static function templateNames(): array
    {
        return array_keys(config('permissions.templates', []));
    }

    /**
     * يتحقق من أن النطاق (scope) معرّف في التطبيق.
     *
     * كيف يعمل: يبحث عن المفتاح (مثل products أو categories) داخل schema().
     *
     * لماذا: يمنع أخطاء الكتابة في الـ Policies أو قاعدة البيانات
     * (مثلاً category بدلاً من categories).
     */
    public static function isScopeValid(string $scope): bool
    {
        return array_key_exists($scope, self::schema());
    }

    /**
     * يتحقق من أن الإجراء (action) موجود ضمن نطاق معيّن.
     *
     * كيف يعمل: يتأكد أولاً من صحة النطاق، ثم يبحث عن الإجراء
     * (مثل view أو edit) داخل actions لذلك النطاق.
     *
     * لماذا: hasPermission() يجب أن يرفض مفاتيح غير معرّفة
     * حتى لا نعتقد أن صلاحية موجودة وهي غير معرّفة في النظام.
     */
    public static function isActionValid(string $scope, string $action): bool
    {
        if (! self::isScopeValid($scope)) {
            return false;
        }

        return array_key_exists($action, self::schema()[$scope]['actions'] ?? []);
    }

    /**
     * اختصار للتحقق من صحة النطاق والإجراء معاً.
     *
     * كيف يعمل: يستدعي isActionValid() التي تغطي كلا الشرطين.
     *
     * لماذا: الـ Policies تستدعي hasPermission('categories', 'edit')
     * وهذه الدالة تضمن أن المفتاحين معرّفان رسمياً في config.
     */
    public static function isValid(string $scope, string $action): bool
    {
        return self::isActionValid($scope, $action);
    }

    /**
     * يحدد إن كان النطاق مخصصاً لـ super_admin فقط.
     *
     * كيف يعمل: يقرأ العلم super_admin_only من تعريف النطاق في الإعدادات.
     *
     * لماذا: site_settings و admin_users لا يُمنحان لمدير عادي (admin)
     * حتى لو حاول أحدهم حفظها في JSON يدوياً.
     */
    public static function isSuperAdminOnlyScope(string $scope): bool
    {
        if (! self::isScopeValid($scope)) {
            return false;
        }

        return (bool) (self::schema()[$scope]['super_admin_only'] ?? false);
    }

    /**
     * يُرجع النطاقات التي يمكن منحها لمدير عادي (admin).
     *
     * كيف يعمل: يفلتر schema() ويستبعد النطاقات ذات super_admin_only = true.
     *
     * لماذا: واجهة تعديل الصلاحيات وemptyMatrix() يجب أن تعرض فقط
     * ما يُسمح بتعيينه لدور admin.
     */
    public static function grantableScopes(): array
    {
        return array_filter(
            self::schema(),
            fn (array $scope): bool => ! ($scope['super_admin_only'] ?? false),
        );
    }

    /**
     * يبني مصفوفة صلاحيات بكل القيم false للنطاقات القابلة للمنح.
     *
     * كيف يعمل: يمر على كل نطاق قابل للمنح وكل إجراء فيه ويضع false.
     *
     * لماذا: نقطة بداية آمنة عند إنشاء مدير جديد أو دعوة admin بصلاحيات مخصصة
     * قبل أن يفعّل super_admin التبديلات المطلوبة.
     */
    public static function emptyMatrix(): array
    {
        $matrix = [];

        foreach (self::grantableScopes() as $scope => $definition) {
            $matrix[$scope] = [];

            foreach (array_keys($definition['actions'] ?? []) as $action) {
                $matrix[$scope][$action] = false;
            }
        }

        return $matrix;
    }

    /**
     * يبني مصفوفة بكل الصلاحيات true للنطاقات القابلة للمنح.
     *
     * كيف يعمل: مثل emptyMatrix() لكن يضع true لكل إجراء.
     *
     * لماذا: قالب full_admin في الـ seeder ومدير المتجر الافتراضي
     * يحتاجان صلاحيات كاملة دون نسخ JSON يدوياً في UserFactory.
     */
    public static function fullMatrix(): array
    {
        $matrix = self::emptyMatrix();

        foreach ($matrix as $scope => $actions) {
            foreach (array_keys($actions) as $action) {
                $matrix[$scope][$action] = true;
            }
        }

        return $matrix;
    }

    /**
     * يبني مصفوفة صلاحيات من قالب جاهز في config (مثل catalogue_manager).
     *
     * كيف يعمل: يبدأ من emptyMatrix() ثم يفعّل true فقط للإجراءات
     * المذكورة في permissions.templates.{name}.
     *
     * لماذا: يسمح بأدوار جاهزة (قراءة فقط، مدير كتالوج، دعم طلبات)
     * بشكل متسق عند الدعوة أو البذر دون تكرار JSON.
     */
    public static function fromTemplate(string $template): array
    {
        $definition = config("permissions.templates.{$template}");

        if (! is_array($definition)) {
            return self::emptyMatrix();
        }

        $matrix = self::emptyMatrix();

        foreach ($definition as $scope => $actions) {
            if (! isset($matrix[$scope]) || ! is_array($actions)) {
                continue;
            }

            foreach ($actions as $action) {
                if (self::isValid($scope, $action)) {
                    $matrix[$scope][$action] = true;
                }
            }
        }

        return $matrix;
    }

    /**
     * ينظّف ويوحّد مصفوفة صلاحيات محفوظة من قاعدة البيانات.
     *
     * كيف يعمل:
     * 1. يبدأ من emptyMatrix() كأساس.
     * 2. يدمج القيم المحفوظة فقط للمفاتيح الصالحة.
     * 3 يتجاهل النطاقات المحظورة على admin و المفاتيح غير المعروفة.
     *
     * لماذا: JSON في users.permissions قد يكون قديماً أو معدّلاً يدوياً؛
     * normalize() تضمن شكلاً آمناً ومتوقعاً قبل الحفظ أو العرض.
     */
    public static function normalize(?array $permissions): array
    {
        $matrix = self::emptyMatrix();

        if (! is_array($permissions)) {
            return $matrix;
        }

        foreach ($permissions as $scope => $actions) {
            if (! isset($matrix[$scope]) || self::isSuperAdminOnlyScope($scope)) {
                continue;
            }

            if (! is_array($actions)) {
                continue;
            }

            foreach ($actions as $action => $allowed) {
                if (self::isValid($scope, $action)) {
                    $matrix[$scope][$action] = $allowed === true;
                }
            }
        }

        return $matrix;
    }

    /**
     * يتحقق إن كان يُسمح بمنح نطاق معيّن لمدير عادي.
     *
     * كيف يعمل: النطاق صالح وليس super_admin_only.
     *
     * لماذا: قبل حفظ صلاحيات من نموذج الدعوة نرفض محاولة
     * منح site_settings لدور admin.
     */
    public static function canGrantScope(string $scope): bool
    {
        return self::isScopeValid($scope) && ! self::isSuperAdminOnlyScope($scope);
    }

    /**
     * يُرجع قائمة الإجراءات المعرّفة لنطاق معيّن.
     *
     * كيف يعمل: يقرأ مفاتيح actions من تعريف النطاق في schema().
     *
     * لماذا: مفيد لبناء واجهة المصفوفة (صف لكل إجراء) دون تكرار القوائم في Vue.
     */
    public static function actionsForScope(string $scope): array
    {
        if (! self::isScopeValid($scope)) {
            return [];
        }

        return array_keys(self::schema()[$scope]['actions'] ?? []);
    }

    /**
     * يُرجع التسمية المعروضة لنطاق (مثل Categories).
     *
     * كيف يعمل: يقرأ حقل label من تعريف النطاق.
     *
     * لماذا: واجهة الصلاحيات تعرض أسماء مقروءة بدلاً من مفاتيح مثل promo_codes.
     */
    public static function labelForScope(string $scope): ?string
    {
        if (! self::isScopeValid($scope)) {
            return null;
        }

        return self::schema()[$scope]['label'] ?? null;
    }

    /**
     * يُرجع التسمية المعروضة لإجراء داخل نطاق (مثل Edit categories).
     *
     * كيف يعمل: يقرأ من actions[action] داخل تعريف النطاق.
     *
     * لماذا: نفس سبب labelForScope — عرض واضح في جدول تبديل الصلاحيات.
     */
    public static function labelForAction(string $scope, string $action): ?string
    {
        if (! self::isActionValid($scope, $action)) {
            return null;
        }

        return self::schema()[$scope]['actions'][$action] ?? null;
    }
}
