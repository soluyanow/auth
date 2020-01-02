<?php

declare(strict_types=1);

namespace Jasny\Auth\Session;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Use PHP sessions to store auth session info.
 */
class PhpSession implements SessionInterface
{
    protected string $key;

    /**
     * PhpSession constructor.
     *
     * @param string $key
     * @param \ArrayAccess<string,mixed>|null $session  Omit to use $_SESSION
     */
    public function __construct(string $key = 'auth')
    {
        $this->key = $key;
    }

    /**
     * Unused, since the super global $_SESSION is use.
     * This middleware doesn't start or modify the session based on the server request.
     *
     * @return $this
     */
    public function forRequest(ServerRequestInterface $request): self
    {
        return $this;
    }

    /**
     * Assert that there is an active session.
     *
     * @throws \RuntimeException if there is no active session
     */
    protected function assertSessionStarted(): void
    {
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            throw new \RuntimeException("Unable to use session for auth info: Session not started");
        }
    }

    /**
     * Get auth information from session.
     *
     * @return array{uid:string|int|null,context:mixed,checksum:string|null}
     */
    public function getInfo(): array
    {
        $this->assertSessionStarted();

        $data = $_SESSION[$this->key] ?? [];

        return [
            'uid' => $data['uid'] ?? null,
            'context' => $data['context'] ?? null,
            'checksum' => $data['checksum'] ?? null,
        ];
    }

    /**
     * Persist auth information to session.
     *
     * @param string|int  $uid
     * @param mixed       $context
     * @param string|null $checksum
     */
    public function persist($uid, $context, ?string $checksum): void
    {
        $this->assertSessionStarted();

        $_SESSION[$this->key] = compact('uid', 'context', 'checksum');
    }

    /**
     * Remove auth information from session.
     */
    public function clear(): void
    {
        $this->assertSessionStarted();

        unset($_SESSION[$this->key]);
    }
}
