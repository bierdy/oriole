<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
        <meta name="format-detection" content="telephone=no">
        <title><?= $title; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" type="text/css">
        <script>
            window.app_config = <?= $appConfig; ?>;
            window.cookie_config = <?= $cookieConfig; ?>;
        </script>
    </head>
    <body>
        <main class="main">
            <div class="container-fluid">
                <div class="row h-100">
                    <div class="col-12 col-lg-auto pt-4 pb-4">
                    </div>
                    <div class="col-auto d-none d-lg-block">
                        <div class="resizer px-1"></div>
                    </div>
                    <div class="col-12 col-lg pt-lg-4">
                        <h1><?= $title; ?></h1>
                        <?= $this->renderSection('template'); ?>
                    </div>
                </div>
            </div>
        </main>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>
        <script src="https://cdn.ckeditor.com/ckeditor5/29.2.0/classic/ckeditor.js"></script>
    </body>
</html>