<?php

namespace Vigilant\Config;

use Vigilant\Exception\ConfigException;

final class CheckEnvs
{
    /**
     * @var array<string, int|string|boolean> $config
     */
    private array $config = [];

    /**
     * Constructor
     *
     * @param array<string, int|string|boolean> $config
     * @param array<int, string> $notificationServices
     */
    public function __construct(array $config, array $notificationServices)
    {
        $this->config = $config;

        $this->quiet();
        $this->feedsFile();
        $this->notificationService($notificationServices);
        $this->notificationGotify();
        $this->notificationNtfy();
    }

    /**
     * Get config
     *
     * @return array<string, int|string|boolean>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Check QUIET
     */
    private function quiet(): void
    {
        if ($this->isEnvSet('QUIET') === true && $this->getEnv('QUIET') === 'true') {
            $this->config['QUIET'] = true;
        }
    }

    /**
     * Check FEEDS_FILE
     *
     * @throws ConfigException if feeds file is not found
     * @throws ConfigException if feeds file is not readable
     */
    private function feedsFile(): void
    {
        if ($this->isEnvSet('FEEDS_FILE') === true) {
            if (file_exists($this->getEnv('FEEDS_FILE')) === false) {
                throw new ConfigException(
                    'Feeds file not found: ' . $this->getEnv('FEEDS_FILE') . ' [VIGILANT_FEEDS_FILE]'
                );
            }

            if (is_readable(self::getEnv('FEEDS_FILE')) === false) {
                throw new ConfigException(
                    'Feeds file is readable: ' . $this->getEnv('FEEDS_FILE') . ' [VIGILANT_FEEDS_FILE]'
                );
            }

            $this->config['FEEDS_FILE'] = $this->getEnv('FEEDS_FILE');
        }
    }

    /**
     * Check NOTIFICATION_SERVICE
     *
     * @param array<int, string> $supportedServices Supported notification services
     *
     * @throws ConfigException if no notification service is given
     * @throws ConfigException if unknown notification service is given
     */
    private function notificationService(array $supportedServices): void
    {
        if ($this->isEnvSet('NOTIFICATION_SERVICE') === false) {
            throw new ConfigException('No notification service given [VIGILANT_NOTIFICATION_SERVICE]');
        }

        $service = strtolower($this->getEnv('NOTIFICATION_SERVICE'));

        if (in_array($service, $supportedServices) === false) {
            throw new ConfigException('Unknown notification service given. [VIGILANT_NOTIFICATION_SERVICE]');
        }

        $this->config['NOTIFICATION_SERVICE'] = $service;
    }

    /**
     * Check NOTIFICATION_GOTIFY_*
     *
     * @throws ConfigException if no Gotify URL is given
     * @throws ConfigException if no Gotify app token is given
     */
    private function notificationGotify(): void
    {
        if ($this->config['NOTIFICATION_SERVICE'] === 'gotify') {
            if ($this->isEnvSet('NOTIFICATION_GOTIFY_URL') === false) {
                throw new ConfigException('No Gotify URL given [VIGILANT_NOTIFICATION_GOTIFY_URL]');
            }

            $this->config['NOTIFICATION_GOTIFY_URL'] = $this->getEnv('NOTIFICATION_GOTIFY_URL');

            if ($this->isEnvSet('NOTIFICATION_GOTIFY_TOKEN') === false) {
                throw new ConfigException('No Gotify app token given [VIGILANT_NOTIFICATION_GOTIFY_TOKEN]');
            }

            $this->config['NOTIFICATION_GOTIFY_TOKEN'] = $this->getEnv('NOTIFICATION_GOTIFY_TOKEN');
        }
    }

    /**
     * Check NOTIFICATION_NTFY_*
     *
     * @throws ConfigException if no Ntfy URL is given
     * @throws ConfigException if no Ntfy topic is given
     * @throws ConfigException if an unknown Ntfy auth type is given
     * @throws ConfigException if no Ntfy auth username is given and is required
     * @throws ConfigException if no Ntfy auth password is given and is required
     */
    private function notificationNtfy(): void
    {
        if ($this->config['NOTIFICATION_SERVICE'] === 'ntfy') {
            if ($this->isEnvSet('NOTIFICATION_NTFY_URL') === false) {
                throw new ConfigException('No ntfy URL given [VIGILANT_NOTIFICATION_NTFY_URL]');
            }

            $this->config['NOTIFICATION_NTFY_URL'] = $this->getEnv('NOTIFICATION_NTFY_URL');

            if ($this->isEnvSet('NOTIFICATION_NTFY_TOPIC') === false) {
                throw new ConfigException('No ntfy topic given [VIGILANT_NOTIFICATION_NTFY_TOPIC]');
            }

            $this->config['NOTIFICATION_NTFY_TOPIC'] = $this->getEnv('NOTIFICATION_NTFY_TOPIC');

            if ($this->isEnvSet('NOTIFICATION_NTFY_AUTH') === true) {
                if (in_array($this->getEnv('NOTIFICATION_NTFY_AUTH'), ['password', 'token']) === false) {
                    throw new ConfigException(
                        'Unknown ntfy authentication method given [VIGILANT_NOTIFICATION_NTFY_USERNAME]'
                    );
                }

                $this->config['NOTIFICATION_NTFY_AUTH'] = $this->getEnv('NOTIFICATION_NTFY_AUTH');

                if ($this->getEnv('NOTIFICATION_NTFY_AUTH') === 'password') {
                    if ($this->isEnvSet('NOTIFICATION_NTFY_USERNAME') === false) {
                        throw new ConfigException(
                            'No ntfy authentication username given [VIGILANT_NOTIFICATION_NTFY_USERNAME]'
                        );
                    }

                    $this->config['NOTIFICATION_NTFY_USERNAME'] = $this->getEnv('NOTIFICATION_NTFY_USERNAME');

                    if ($this->isEnvSet('NOTIFICATION_NTFY_PASSWORD') === false) {
                        throw new ConfigException(
                            'No ntfy authentication password given [VIGILANT_NOTIFICATION_NTFY_PASSWORD]'
                        );
                    }

                    $this->config['NOTIFICATION_NTFY_PASSWORD'] = $this->getEnv('NOTIFICATION_NTFY_PASSWORD');
                }

                if ($this->getEnv('NOTIFICATION_NTFY_AUTH') === 'token') {
                    if ($this->isEnvSet('NOTIFICATION_NTFY_TOKEN') === false) {
                        throw new ConfigException(
                            'No ntfy authentication token given [VIGILANT_NOTIFICATION_NTFY_TOKEN]'
                        );
                    }

                    $this->config['NOTIFICATION_NTFY_TOKEN'] = $this->getEnv('NOTIFICATION_NTFY_TOKEN');
                }
            }
        }
    }

    /**
     * Check if a environment variable is set
     *
     * @return bool
     */
    private function isEnvSet(string $name): bool
    {
        if (getenv('VIGILANT_' . $name) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get environment variable value
     *
     * @param string $name Environment variable name
     * @return mixed
     */
    private function getEnv(string $name): mixed
    {
        return getenv('VIGILANT_' . $name);
    }
}
