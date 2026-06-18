<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Service\Education\Family;

use App\Model\Education\Family\EducationFamilyServiceAttachment;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Family\HomeworkRepository;
use App\Service\Education\Academic\GuardianMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;

final class AttachmentAccessService
{
    public function __construct(
        private readonly HomeworkRepository $homeworkRepository,
        private readonly GuardianMobileContextResolver $guardianResolver
    ) {}

    public function canAccess(EducationFamilyServiceAttachment $attachment, EducationUserContext $context): bool
    {
        if ((int) $attachment->tenant_id !== (int) $context->tenantId) {
            return false;
        }
        if ($context->roleCode !== EducationRoleCode::Guardian) {
            return true;
        }
        if ($attachment->student_id === null) {
            return false;
        }

        $guardian = $this->guardianResolver->resolveGuardian($context);

        return $this->homeworkRepository->isGuardianBound((int) $context->tenantId, (int) $attachment->student_id, (int) $guardian->id);
    }
}
