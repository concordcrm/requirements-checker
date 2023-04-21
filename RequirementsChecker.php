<?php

class RequirementsChecker
{
    /**
     * @var array
     */
    protected $requirements = [];

    /**
     * Initialize new RequirementsChecker instance
     *
     * @param array $requirements
     */
    public function __construct($requirements)
    {
        $this->requirements = $requirements;
    }

    /**
     * Check the requirements
     *
     * @return array
     */
    public function check()
    {
        $results = [
            'results' => [
                'php'       => [],
                'functions' => [],
                'apache'    => [],
            ],
            'recommended' => [
                'php' => [],
                'functions' => [],
            ],
            'error' => false,
        ];

        $requirements = $this->requirements['requirements'];

        foreach ($requirements as $type => $requirement) {
            switch ($type) {
                // check php requirements
                case 'php':
                    $checks = $this->checkPHPRequirements($requirements[$type]);

                    $results['results'][$type] = array_merge($results['results'][$type], $checks);

                    if ($this->determineIfFails($checks)) {
                        $results['error'] = true;
                    }

                break;

                case 'functions':
                    $checks = $this->checkPHPFunctions($requirements[$type]);

                    $results['results'][$type] = array_merge($results['results'][$type], $checks);

                    if ($this->determineIfFails($checks)) {
                        $results['error'] = true;
                    }

                break;

                // check apache requirements
                case 'apache':
                    foreach ($requirements[$type] as $requirement) {
                        // if function doesn't exist we can't check apache modules
                        if (function_exists('apache_get_modules')) {
                            $results['results'][$type][$requirement] = true;

                            if (! in_array($requirement, apache_get_modules())) {
                                $results['results'][$type][$requirement] = false;

                                $results['errors'] = true;
                            }
                        }
                    }

                    break;
                case 'recommended':
                    $results['recommended']['php'] = $this->checkPHPRequirements($requirements[$type]['php']);
                    $results['recommended']['functions'] = $this->checkPHPFunctions($requirements[$type]['functions']);

                break;
            }
        }

        return $results;
    }

    /**
     * Check the php requirements
     *
     * @param  array $requirements
     *
     * @return array
     */
    protected function checkPHPRequirements($requirements)
    {
        $results = [];

        foreach ($requirements as $requirement) {
            $results[$requirement] = extension_loaded($requirement);
        }

        return $results;
    }

    /**
     * Check the PHP functions requirements
     *
     * @param array $functions
     *
     * @return array
     */
    protected function checkPHPFunctions($functions)
    {
        $results = [];

        foreach ($functions as $function) {
            $results[$function] = in_array($function, get_defined_functions()['internal']);
        }

        return $results;
    }

    /**
     * Determine if all checks fails
     *
     * @param array $checks
     *
     * @return boolean
     */
    protected function determineIfFails($checks)
    {
        return count(array_filter($checks)) !== count($checks);
    }

    /**
     * Check PHP version requirement.
     *
     * @return array
     */
    public function checkPHPversion()
    {
        $minVersionPhp     = $this->requirements['core']['minPhpVersion'];
        $currentPhpVersion = static::getPhpVersionInfo();
        $supported         = version_compare($currentPhpVersion['version'], $minVersionPhp) >= 0;

        $phpStatus = [
            'full'      => $currentPhpVersion['full'],
            'current'   => $currentPhpVersion['version'],
            'minimum'   => $minVersionPhp,
            'supported' => $supported,
        ];

        return $phpStatus;
    }

    /**
     * Get current Php version information.
     *
     * @return array
     */
    protected static function getPhpVersionInfo()
    {
        $currentVersionFull = PHP_VERSION;
        preg_match("#^\d+(\.\d+)*#", $currentVersionFull, $filtered);
        $currentVersion = $filtered[0];

        return [
            'full'    => $currentVersionFull,
            'version' => $currentVersion,
        ];
    }
}