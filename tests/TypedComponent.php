<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\Component;
use Components\Hotfire\Responses\DownloadResponse;
use Components\Hotfire\Responses\FlashMessage;
use Components\Hotfire\Responses\NoContentResponse;
use Components\Hotfire\Responses\RedirectResponse;

final class TypedComponent extends Component
{
    public string $uninitialized;
    public ?string $nullable = null;
    public int $total = 10;
    public array $tags = [];
    public array $categories = [];
    public string $status = 'active';
    public string $email = '';
    public array $user = ['name' => 'Initial'];
    public array $customMessages = [];
    protected array $lockedProperties = [];
    private int $internalCounter = 0;

    public function computedProperties(): array
    {
        return ['totalCount'];
    }

    public function getTotalCount(): int
    {
        return $this->total + $this->internalCounter;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'total' => 'required|min:1',
        ];
    }

    public function add(int $step): void
    {
        $this->total += $step;
    }

    public function subtract(int $step): void
    {
        $this->total -= $step;
    }

    public function redirectToHome(): RedirectResponse
    {
        return $this->redirect('/home');
    }

    public function flashSuccess(): FlashMessage
    {
        return $this->flash('Operation successful', 'success');
    }

    public function downloadFile(): DownloadResponse
    {
        return $this->download('file content', 'test.txt', 'text/plain');
    }

    public function returnNoContent(): NoContentResponse
    {
        return $this->noContent();
    }
}
