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

namespace App\Service\Education\Standards;

use App\Repository\Education\Standards\CourseFeedbackRepository;

final class CourseFeedbackService
{
    public function __construct(private readonly CourseFeedbackRepository $feedback) {}

    /**
     * @param array<string, mixed> $data
     * @return array{feedback_id: int}
     */
    public function create(array $data): array
    {
        $record = $this->feedback->create($data);

        return ['feedback_id' => (int) $record->id];
    }
}
