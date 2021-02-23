<?php

use App\Template\CommonUtils;

/**
 * Template args from other included templates or from controller
 * @var string[]
 */
global $tpl;

CommonUtils::setDefaultTemplates();

echo <<<TPL
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$tpl['title']}</title>
        {$tpl['stylesheets']}
    </head>
    <body>
        {$tpl['navbar']}
        
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-xs-12 col-md-10 col-lg-8 col-xl-7 col-xxl-6">
                    {$tpl['body']}
                </div>
            </div>
        </div>
        
        {$tpl['scripts']}
    </body>
    </html>
TPL;