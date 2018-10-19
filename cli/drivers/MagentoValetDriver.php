<?php

class MagentoValetDriver extends BasicValetDriver
{

    /**
     * Checks sitempath to check if codebase is in ./ or ./public/
     * @param  string $sitePath
     * @return string
     */
    private function mapSitePath($sitePath)
    {
        if(is_dir($sitePath.'/public/app/code/core/Mage')) {
            return $sitePath . "/public";
        }
        return $sitePath;
    }

    /**
     * Determine if the driver serves the request.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return bool
     */
    public function serves($sitePath, $siteName, $uri)
    {
        $sitePath = $this->mapSitePath($sitePath);
        return is_dir($sitePath.'/app/code/core/Mage');
    }

    public function configure($devtools, $url) {
        info('Configuring Magento...');

        $sitePath = getcwd();
        $sitePath = $this->mapSitePath($sitePath);

        if(!file_exists($sitePath.'/app/etc/local.xml')) {
            info('local.xml missing. Installing default local.xml...');
            $devtools->files->putAsUser(
                $sitePath.'/app/etc/local.xml',
                str_replace(
                    'DBNAME',
                    $devtools->mysql->getDirName(),
                    $devtools->files->get(__DIR__.'/../stubs/magento/local.xml')
                )
            );
        }

        info('Setting base url...');
        $devtools->cli->quietlyAsUser('n98-magerun config:set web/unsecure/base_url ' . $url . '/');
        $devtools->cli->quietlyAsUser('n98-magerun config:set web/secure/base_url ' . $url . '/');

        info('Flushing cache...');
        $devtools->cli->quietlyAsUser('n98-magerun cache:flush');

        info('Configured Magento');
    }
}
