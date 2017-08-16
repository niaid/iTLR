<?php

require_once('../app/includes.php');
?>
<!-- The Home Page of the iTLR Website -->
<!DOCTYPE html>
<html>
    <head>
        <title>iTLR: interactive Toll-Like Receptor</title>
        <meta name="description" content="An interactive Toll-Like Receptor Website Application where one can compare experiments and compare genes"/>
        <link rel="stylesheet" href="Assets/CSS/splash.css"/>
    </head>
    <body>
        <header>
            <a href="https://www.niaid.nih.gov/">
                <img src="Assets/Images/NIH_NIAID_logo.jpg" alt="NIH > NIAID logo" class="header__image"/>
            </a>
            <div class="header__links">
                <a href="https://www.nih.gov"
                   class="header__link">National Institutes of Health</a> >
                <a href="https://www.niaid.nih.gov/Pages/default.aspx"
                   class="header__link">National Institute of Allergy and Infectious Diseases</a> >
                <a href="https://www.niaid.nih.gov/labsandresources/labs/aboutlabs/lsb/pages/default.aspx?wt.ac=bcLsb"
                   class="header__link">Laboratory of Systems Biology</a>
            </div>
        </header>
        <section>
            <div>
                <div class="section__intro">
                    <span class="section__title">Welcome to iTLR!</span><br/>
                    <span class="section__subTitle">An <b><u>i</u></b>nteractive <b><u>T</u></b>LR (Toll-Like Receptor) <b><u>L</u></b>abratory <b><u>R</u></b>esource website application</span><br/>
                    <!--<span style="color:red;">Website is currently being edited. You may experience some issues.</span>-->
                </div>
            </div>
            <div class="options">
                <div class="option  utility__left">
                    <h2 class="option__title_div"><a href="experiments.php" class="option__title">Compare Experiments:</a></h2>
                    <p class="option__description">
                        Analyze genome-scale response of a small subset of selected experiments to elucidate the effect of
                        stimulations, molecular fingerprints, cell types, and response dynamics.
                    </p>
                    <div>
                        <input type="button" class="button" value="Tutorial"/>
                        <input type="button" class="button utility__right" value="Tool" onclick="tool('experiments');"/>
                    </div>
                </div>
                <div class="option utility__right">
                    <h2 class="option__title_div"><a href="genes.php" class="option__title">Compare Genes:</a></h2>
                    <p class="option__description">
                        Display the response of a small subset of genes and proteins under diverse experimental conditions.
                    </p>
                    <div>
                        <input type="button" class="button" value="Tutorial"/>
                        <input type="button" class="button utility__right" value="Tool" onclick="tool('genes');"/>
                    </div>
                </div>
            </div>
        </section>
        <footer>
            <div class="footer__images">
                <a href="http://www.hhs.gov">
                    <img src="Assets/Images/HHS_Logo.gif" alt="United States Department of Health and Human Services Logo"
                         class="footer__image"/>
                </a>
                <a href="https://www.nih.gov">
                    <img src="Assets/Images/NIH_Logo.png" alt="National Institutes of Health Logo"
                         class="footer__image"/>
                </a>
                <a href="https://www.usa.gov/">
                    <img src="Assets/Images/USA.gov_Logo.png" alt="USA.gov Logo"
                         class="footer__image" style="padding-left:5px;"/>
                </a>
            </div>
        </footer>
    <script>
        function tool(page) {
            window.location = page + '.php';
        }
    </script>
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-97388395-1', 'auto');
            ga('send', 'pageview');

        </script>
    </body>
</html>