<?php
/**
 * index.php — WordPress-Fallback-Template
 * Wird nur genutzt wenn kein spezifischeres Template greift.
 */
get_header();
?>

<main class="site-main">
    <div class="container" style="padding-top: 6rem; padding-bottom: 6rem;">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <div class="kurs-body"><?php the_content(); ?></div>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
