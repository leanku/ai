<?php
declare(strict_types=1);

namespace Leanku\Ai\Contracts;

/**
 * 统一的响应接口。
 */
interface ResponseInterface
{
    // 获取响应内容
    public function getContent(): string;

    // 获取消息角色。
    public function getRole(): string;

    // 获取使用的模型。
    public function getModel(): string;

    // 获取使用情况统计。
    public function getUsage(): array;

    // 获取原始响应数据。
    public function getData(): array;

    // 检查请求是否成功。
    public function isSuccessful(): bool;

    // 获取完成原因。
    public function getFinishReason(): ?string;
}