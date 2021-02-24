<?php

declare(strict_types=1);

use App\Service\App;
use App\Service\DIContainer\Container;

$devEnv = (Container::getStatic(App::class))->isDevEnv();

?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Hyphenator 3000</a>
        
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">Words</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pattern">Patterns</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://github.com/mindaugasw/visma-praktika" target="_blank" rel="noreferrer">About</a>
                </li>
            </ul>
        </div>

        <?php if ($devEnv) { ?>
             <!--Bootstrap breakpoints indicator-->
            <span class='mx-0 px-0'>
                <button type="button" class='btn btn-danger d-block d-sm-none' disabled>
                    XS
                </button>
                <button type="button" class='btn btn-warning d-none d-sm-block d-md-none' disabled>
                    SM
                </button>
                <button type="button" class='btn btn-success d-none d-md-block d-lg-none' disabled>
                    MD
                </button>
                <button type="button" class='btn btn-primary d-none d-lg-block d-xl-none' disabled>
                    LG
                </button>
                <button type="button" class='btn btn-dark d-none d-xl-block d-xxl-none' disabled>
                    XL
                </button>
                <button type="button" class='btn btn-warning d-none d-xxl-block' disabled>
                    XXL
                </button>
            </span>
        <?php } ?>
    </div>
</nav>