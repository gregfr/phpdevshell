<?php

/**
 * This page will be displayed whenever an unhandled error or exception occurs in PHPDevShell
 *
 * @version 2.5
 *
 * @date 20130726 (2.5) (greg) updated to display encapsulated exceptions
 */
    $skin = empty($this->configuration['skin']) ? '': $this->configuration['skin'];
    $navigation = $this->navigation;
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>Serious Error Encountered</title>
        <meta charset=UTF-8>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="keywords" content="critical, error">
        <meta name="description" content="We encountered an error">
        <link rel="stylesheet" href="<?php echo $aurl ?>/themes/cloud/css/reset.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo $aurl ?>/themes/cloud/jquery/css/ui-lightness/jquery-ui.css?v314a" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo $aurl ?>/themes/cloud/css/combined.css?v=314a" type="text/css" media="screen" />
        <script type="text/javascript" src="<?php echo $aurl ?>/themes/cloud/PHPDS-combined.min.js?v=314a"></script>
        <script type="text/javascript" src="<?php echo $aurl ?>/themes/cloud/js/showhide/jquery.showhide.js"></script>
        <style>
            #backtrace td, #backtrace th{
                border: none;
            }
            .bt-line-number {
                color: #F93;
            }
            .causes_list {
                list-style-type: square;
            }
            H1 {
                font-size: 3em !important;
                text-align: center;
            }
            H2 {
                font-size: 2em !important;
            }
            H3 {
                font-size: 1.5em !important;
            }
            #support {
                position: absolute;
                right: 10px;
                top: 10px;
                font-size: 120%;
                text-align: center;
            }
            a:link {
                text-decoration: underline !important;
            }
            .jumps {
                font-size: xx-small;
                float: right;
            }

            .jumps IMG {
                float: none;
            }
        </style>
    </head>
    <!-- PHPDevShell Main Body -->
    <body class="ui-widget">
        <?php

        /* @var PHPDS_dependant $this */
        /* @var PHPDS_navigation $navigation */
        /* @var string $bt */

        $jumps = ' <span class="jumps">
            <a href="#therror">the error</a> -
            <a href="#thebacktrace">backtrace</a> -
            <a href="#configuration">configuration</a> -
            <a href="#classregistry">class registry</a> -
            <a href="#phpinfo">PHP info</a> -
            <a href="/"><img width="16" height="16" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAApJJREFUeNp8U11IU2EYfs8531kHZSlkrQs1w1JKiUZm1JrR+hPZhcywECLropQwKgpSKJMks2AEI/TK2kUXEZFQXph4UZqDQWRETbxokjTLiTabes7Z4PS+3+ZJIfrgOXvO8z7P+33vx46wAQAEgiCkftPAtR9xKUXhQd56GM7LMSqQ5yBmEG+pwODfi4yXvV1d1fRypbFRmoyCeK7eXXq6pfuh/07Dhf81OGAAXL3v87nLygq5cM/nq77W1MRC4dj0QmwG4vFFedksZa4e4SDS6x1eb5XDsT2lIXJz18HWbfaiD6FJe5Y1A8Kh4KCq6yOIVSc4hDu33O7sdDmdpRB43AFZ5Sd4IRZ8Cs76ZkgkEuD3P4c1ifld0egcGCtGOGIYxo2b7e1Ol8sOsizDws8JKHHW8mLkZSfXqKbjrq/7+0+OhsZ6sMEANTiK5FZLa+veyso9oCiKeaTsDIvJJUniIA+dJDA01EaTi/hoX8vYm3fP2s7bbDbTmGkrgO/BPg7iyzp5yEsZyjLcvbwgPwc8NfsaRFHkJlrHLnaYuxcX/+Xk8dR4xLknI80fv/4AsRDDp2rLQU8kRRHDjDGO8eAweM9UcxBf1slDXspQlp09vhs0PcGbC2LKROv9ix7wuB2cB5DvqDjMOXnQT6MDZZmq6eY9rRzB0Bf5jVutVtiyaaOpkwczkjnSl4lpWFI1AjaQzMtSFAuoqgqzs/PQ2zto6uQhL2UoK3b3fYKxbzMoqpKwqoHMGygKg7q6KlMXeANVogxlaZasRwNjtvjCEhOYBURLBofFIoOmaRCJRGBqasrUyUNeylCWPoN8+s/sxG8gG6CI5voF8Oo3YyVIw+lRN1uTyc9Yd6fr46MAd4n+EWAAzpL44JhgHp8AAAAASUVORK5CYII=" /></a> -
            <a href="system-management.html"><img width="16" height="16" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAoJJREFUeNqkU01oE1EQnk02iTFQE7QihUKRkKTF1iU9+FdQCoWYgAcPegkIeiiIWiHgwUvpQXs1Ggo99OYlFwUhWAhYhZJWUmhMxJbYYk1LFDcmJraSv911vjQbevPgg9kZ5vu+eW9n3hM0TaP/WSI+gUCADAYDmUwmEgSBUNRoNJ5jaKjNSyuKsqRjjUaDVFWlWCy2X0BfDJ5nd5r9KxZI0Wh0BuRgMHibcznGrrD/wD6hawwHxBdcLte12dnZGYfDcYOFhkJBpnL5F3Y0IAcMHHB1nYAj+Xw+xHeZ8FSWf1BPTw+trqY2JElyAkilUhsej8dZKhWpu/s4jY+P3+P0s/n5+f0TVCoVqlarL0Oh0KTZbCZZlmlgoN+pqgrBEO/u/iZg4IALTecX+BQX6/X69Xw+v8e7bYqiSMvLy+t+f2AGhhg5YOCAC43+7+T1eh+srCS1hYU32tJSQkun09rg4NA0TwLTIMTIAQMHXGigbU2hVqsZq9UaNZsKKYrKoxRZKDYwKizEyAEDB1xoOk3kzo6xP4PExMT9WyMjl/q2t7+npqYevkBucvLx1d7eE9Li4tutcPjJXEsoCO+z2WxcP0GcC3zmDt8ZHj7bVyyWyO32SLHYOwl4ufyTdna+ELCuriN2nlSEC2x1mshdRZGbkchcSJaLfCOtFI+//prLbRIMMXLAwAEXmk4T+ZLALo+Ojj1PJtc1t7s/bLfbHyUSGQ2GGDlg4IALTesd6Y8JY7JarX6bzTZtsVhOwq+tfdMymZx2MAcOuPrmrSYKaDHRUbZjbIcA8sM6xQ9sADFP4xNf54/t21tnk9kKrG3qBdCLw20T//GCFbY9tj+sVf8KMAACOoVxz9PPRwAAAABJRU5ErkJggg==" /></a>
            </span>';


        if (!empty($message)) {
            ?>
            <h1>An error occured</h1>
            <p class="note">This page will try to provide as much information as possible so you can track down (and hopefully fix) the problem.</p>
            <?php
                if (is_a($e, 'PHPDS_exception')) {
                    /* @var PHPDS_exception $e */
                    $re = $e->getRealException();
                    if ($re->hasCauses()) {
                        @list($msg, $causes, $extra_html) = new PHPDS_array($re->getCauses());
                        ?>

                        <article class="ui-widget-content ui-corner-all" style="margin:2em; padding:2em;">
                        <h3 class="warning"><?php  echo $msg?></h3>
                        <p>Possible causes are:</p>
                        <ul id="causes_list">
                        <?php
                            foreach($causes as $cause) {
                                list($title, $text) = $cause;
                                echo "<li><strong>$title</strong><br />$text</li>";
                            }
                            if ($extra_html) {
                                echo $extra_html;
                            }
                        ?>
                        </ul>
                        </article>
                        <?php
                    }
                    if ($re->hasMoreInfo()) {
                        ?>
                        <article class="ui-widget-content ui-corner-all" style="margin:2em; padding:2em;">
                        <h3 class="warning">More information</h3>
                        <p><?php echo $re->getMoreInfo() ?></p>
                        </article>
                        <?php
                    }
                }

                $config = $this->configuration;
            ?>
        <article class="ui-widget-content ui-corner-all" style="margin:2em; padding:2em;">

            <div style="display: none" >
                <p class="warning">WARNING! several errors were caught in the Exception Handler itself:</p>
                <blockquote>
                    <pre id="crumbs"><crumbs></crumbs></pre>
                </blockquote>
                <script>
                    $(function(){
                        var crumbs = $('#crumbs');
                        if (crumbs.html()) crumbs.parents('DIV').show();
                    });
                </script>
            </div>

            <h2><a name="theerror">The error</a><?php echo $jumps ?></h2>

            <p>The error occured on <?php echo date('Y-M-d') ?> at <?php echo date('H:s') ?>.</p>


            <?php
            while (is_a($e, 'Exception')) {
            ?>
                <p><strong>The exception class is "<tt><?php echo get_class($e) ?>"</tt></strong> and the error code is <?php echo $code ?>. The content of the exception is as follow:</p>
                <blockquote>
                <p class="critical"><?php echo $e->getMessage(); ?></p>
                <p><?php if (method_exists($e, 'getExtendedMessage')) echo $e->getExtendedMessage(); ?></p>
                </blockquote>
            <?php
                if (is_a($e, 'PHPDS_Exception')) {
                    $e = $e->getPreviousException();
                    if (is_a($e, 'Exception')) {
                        ?>
                        <hr/>
                        <p><em>Another exception were found encapsulated inside!</em></p>
                        <?php
                    }
                } else {
                    $e = $e->getPrevious();
                }
            }
            ?>


            <p>
            <?php
            if (!empty($config['m'])) {
                echo 'The current menu id is '.$config['m'];
                echo '. <a href="'.$navigation->buildURL(3440897808, 'em='.$config['m']).'">'._('Edit this menu').'</a>';
            } else {
                echo 'It happened outside a menu.';
            }
            ?>
            </p>

            <p>The error <em><strong>actually</strong></em> occurred in file <strong><tt><?php echo $filepath?></tt></strong> at line <strong><?php echo $lineno?></strong> (see the <a href="#thebacktrace">backtrace</a>)</p>
            <blockquote>
            <p><?php echo $filefragment?></p>
            </blockquote>
            <?php if ($ignore >= 0) { ?>
                <p>The origin of the error is <em><strong>probably</strong></em> in file <strong><tt><?php echo $frame['file']?></tt></strong> at line <strong><?php echo $frame['line']?></strong></p>
                <blockquote>
                <p><?php echo $framefragment?></p>
                </blockquote>
            <?php }?>

            <h2><a name="thebacktrace">The Backtrace</a><?php echo $jumps ?></h2>
            <p>All relative file pathes are relative to the server root (namely <tt><?php echo $_SERVER['DOCUMENT_ROOT']; ?>/ )</tt></p>
            <p>Click on the Book icon to access online documentation.</p>
            <table id="backtrace">
                <thead>
                    <tr>
                        <th>File path (relative)</th>
                        <th>Line</th>
                        <th>Call (with arguments)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $bt; ?>
                </tbody>
            </table>


            <h2><a name="configuration">The configuration</a><?php echo $jumps ?></h2>
            <div class="info">
                <h3>Configuration files actually used:</h3>
                <?php echo $conf['used']; ?>
                <h3>Configuration files which would have been used if they were present:</h3>
                <?php echo $conf['missing']; ?>
                <h3>Main database info</h3>
                <?php
                    $db_settings = $this->db->getDBSettings();
                    echo "<p>Database <strong>{$db_settings['database']}</strong> with prefix <strong>{$db_settings['prefix']}</strong> on host <strong>{$db_settings['host']}</strong> with user <strong>{$db_settings['username']}</strong>.</p>";
                ?>
                <h3>Other useful configuration settings</h3>
                <?php
                    echo '<p>Sef URL is <strong>'.($config['sef_url'] ? 'on' : 'off').'</strong> ; default template is '.($config['default_template'] ? '<strong>'.$config['default_template'].'</strong>' : '<b>not set</b>').'.</p>';
                    echo '<p>Guest role  is '.(empty($config['guest_role'])  ? '<i>not set</i>' : '<strong>'.$config['guest_role'].'</strong>').' ; guest group is '.(empty($config['guest_group'])  ? '<i>not set</i>' : '<strong>'.$config['guest_group'].'</strong>').'.</p>';
                    list($plugin, $menu_link) = $navigation->menuPath();
                    echo empty($plugin) ? '<p>No plugin currently selected</p>' : '<p>Current plugin is <strong>'.$plugin.'</strong> (path is <strong>'.$menu_link.')</strong></p>';
                    echo '<p>BASEPATH is <tt><strong>'.BASEPATH.'</strong></tt> - Current working directory is <tt><strong>'.getcwd().'</strong></tt>.</p>';
                ?>
            </div>

            <h2><a name="classregistry">Class Registry</a><?php echo $jumps ?></h2>
            <div class="info">
                <?php
                    echo PU_dumpArray($this->classFactory->PluginClasses);
                ?>
            </div>

            <h2>Included files<?php echo $jumps ?></h2>
            <div class="info">
                <?php
                echo PU_dumpArray(get_included_files());
                ?>
            </div>


            <h2><a name="phpinfo">PHP info</a><?php echo $jumps ?></h2>
            <blockquote>
            <div style="overflow: hidden;">
            <?php phpinfo(); ?>
            </div>
            </blockquote>

        </article>
        <div id="support" class="critical">
            Need support?<br>
            The <a href="http://www.phpdevshell.org/support" target="www.phpdevshell.org">support page</a> of our website is here for you.
        </div>
        <?php } else { ?>
        <article class="ui-widget-content ui-corner-all" style="margin:2em; padding:2em;">
            <h1 class="ui-state-error ui-corner-all">An error has occured...</h1>
            <p>An error has occurred while trying to provide you with the requested resource.</p>
            <p>The site administrator have been informed and will fix the problem as soon as possible.</p>
            <p>Sorry for the inconvenience, please come back later...</p>
        </article>
        <?php }?>
    </body>
</html>
