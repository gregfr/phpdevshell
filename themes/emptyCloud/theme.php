<?php
/* @var PHPDS_template $template */
/* @var PHPDS_configuration $configuration */
?>
<!DOCTYPE HTML>
<html lang="<?php $template->outputLanguage() ?>">
<head>
    <title><?php $template->outputTitle() ?></title>
    <meta charset=<?php $template->outputCharset() ?>>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="keywords" content="<?php $template->outputMetaKeywords() ?>">
    <meta name="description" content="<?php $template->outputMetaDescription() ?>">
    <?php require_once $this->core->themePath('cloud').'/include.php'; ?>
    <?php $template->outputNotifications() ?>
    <!-- Custom Head Added -->
    <?php $template->outputHead() ?>
</head>
<!-- Main Body -->
<body id="container" class="ui-widget ui-widget-content"
      style="<?php echo $configuration['elegant_loading'] ?>">

    <?php $template->outputController() ?>
    <?php $template->outputFooterJS() ?>
    <script type="text/javascript">
        $(document).ready(function ()
        {
            $('body').fadeIn('fast', function ()
            {
                PHPDS.documentReady();
                $(document).trigger('PHPDS_start', PHPDS);
            });
        });
    </script>
</body>
</html>