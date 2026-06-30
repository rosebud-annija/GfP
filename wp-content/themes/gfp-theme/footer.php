</main><!-- #main-content -->

<?php
$ft_brand_claim = gfp_hp('ft_brand_claim', 'Superheroes, wie wir.');
$ft_brand_text  = gfp_hp('ft_brand_text',  'Gesellschaft für Personalentwicklung — Wir entwickeln Menschen, Teams und Organisationen seit über 30 Jahren.');
$ft_linkedin    = gfp_hp('ft_linkedin',    '#');
$ft_instagram   = gfp_hp('ft_instagram',   '#');

$kurse_archiv_url = get_post_type_archive_link('kurs') ?: '/kurse/';
$kontakt_page     = get_page_by_path('kontakt');
$kontakt_url      = $kontakt_page ? get_permalink($kontakt_page) : home_url('/kontakt/');

$programme = [
    'Leadership & Führung',
    'Facilitation & Moderation',
    'Teams & Collaboration',
    'Organisation & Transformation',
    'Personality & Skills',
];
?>

<footer class="site-footer" style="padding:64px var(--section-px) 36px;text-align:left;">
    <div class="ft-inner">
        <div class="ft-top">
            <div>
                <div class="ft-brand-logo">GfP</div>
                <div class="ft-brand-claim"><?php echo esc_html($ft_brand_claim); ?></div>
                <p class="ft-brand-p"><?php echo esc_html($ft_brand_text); ?></p>
                <div class="ft-social">
                    <a href="<?php echo esc_url($ft_linkedin); ?>" class="fsoc" aria-label="LinkedIn">in</a>
                    <a href="<?php echo esc_url($ft_instagram); ?>" class="fsoc" aria-label="Instagram">ig</a>
                </div>
            </div>
            <div class="ft-col">
                <h4>Programme</h4>
                <ul>
                    <?php foreach ($programme as $name) : ?>
                        <li>
                            <a href="<?php echo esc_url($kurse_archiv_url . '#' . rawurlencode(mb_strtolower($name, 'UTF-8'))); ?>">
                                <?php echo esc_html($name); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="ft-col">
                <h4>Kontakt</h4>
                <ul>
                    <li><a href="<?php echo esc_url($kontakt_url); ?>">Kontakt</a></li>
                    <li><a href="#">Impressum</a></li>
                    <li><a href="#">Datenschutz</a></li>
                </ul>
            </div>
        </div>
        <div class="ft-bottom">
            <p>&copy; <?php echo date('Y'); ?> Gesellschaft für Personalentwicklung GmbH</p>
            <span class="ft-bottom-claim">Superheroes, wie wir.</span>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
