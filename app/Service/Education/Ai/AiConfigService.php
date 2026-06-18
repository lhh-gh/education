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

namespace App\Service\Education\Ai;

use App\Repository\Education\Ai\AiConfigRepository;

final class AiConfigService
{
    public function __construct(private readonly AiConfigRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, config_code: string, api_key_visible: false}
     */
    public function saveModelConfig(array $data): array
    {
        if (isset($data['api_key'])) {
            $data['api_key_ciphertext'] = base64_encode((string) $data['api_key']);
            unset($data['api_key']);
        }
        $config = $this->repository->saveModelConfig($data);

        return ['id' => (int) $config->id, 'config_code' => (string) $config->config_code, 'api_key_visible' => false];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, feature_code: string}
     */
    public function saveFeatureSetting(array $data): array
    {
        $setting = $this->repository->saveFeatureSetting($data);

        return ['id' => (int) $setting->id, 'feature_code' => (string) $setting->feature_code];
    }
}
