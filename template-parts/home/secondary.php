<?php
/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion 
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual, 
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */
$sec_cats = array(
    'deportes' => 'Deportes', 'cultura-y-entretenimiento' => 'Cultura', 
    'ciencia-y-tecnologia' => 'Ciencia', 'opinion' => 'Opinión', 
    'salud' => 'Salud', 'educacion' => 'Educación', 
    'servicios-publicos' => 'Servicios', 'comunidad' => 'Comunidad',
    'sucesos' => 'Sucesos', 'nacional' => 'Nacional', 
    'mundo' => 'Mundo', 'economia' => 'Economía', 
    'politica-nacional,politica-monagas' => 'Política'
);

ob_start();
foreach($sec_cats as $slug => $name) :
    // Soportar múltiples slugs separados por coma
    $slugs = explode(',', $slug);
    $term_ids = array();
    $cat_link = '#';

    foreach($slugs as $s) {
        $cat_obj = get_category_by_slug( trim($s) );
        if ( $cat_obj ) {
            $term_ids[] = $cat_obj->term_id;
            // El enlace de la tarjeta será el de la primera categoría válida (e.g. politica)
            if ( $cat_link === '#' ) {
                $cat_link = esc_url( get_category_link( $cat_obj->term_id ) );
            }
        }
    }

    // Saltar si ninguna de las categorías existe
    if ( empty($term_ids) ) continue;

    $sec_q = new WP_Query( array(
        // Auditoría fix: category__in NO incluye hijos. tax_query con
        // include_children => true sí lo hace, mostrando artículos de
        // subcategorías (ej. Política Monagas dentro de Política).
        'tax_query'              => array(
            array(
                'taxonomy'         => 'category',
                'field'            => 'term_id',
                'terms'            => $term_ids,
                'include_children' => true,
                'operator'         => 'IN',
            ),
        ),
        'posts_per_page'         => 1,
        'post_status'            => 'publish',
        'orderby'                => array( 'date' => 'DESC', 'ID' => 'DESC' ),
        'ignore_sticky_posts'    => 1,
        'cache_results'          => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'no_found_rows'          => true,
    ) );
    if( $sec_q->have_posts() ) : ?>
        <div class="sec-card" style="background:var(--color-bg-secondary); border:1px solid var(--color-border); border-top:4px solid var(--color-primary); border-radius:var(--border-radius); overflow:hidden; display:flex; flex-direction:column; box-shadow:var(--shadow-sm); transition:transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='var(--shadow-lg)';" onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-sm)';">
            
            <div style="padding: 15px 20px 10px; display:flex; align-items:center; justify-content:space-between;">
                <a href="<?php echo $cat_link; ?>" style="color:var(--color-primary); text-decoration:none; font-family:var(--font-ui); font-size:0.85rem; font-weight:800; text-transform:uppercase; letter-spacing:1px;"><?php echo esc_html($name); ?></a>
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem; color:var(--color-text-muted);">arrow_forward</span>
            </div>

            <div style="padding: 0 20px 20px; flex-grow:1; display:flex; flex-direction:column;">
                <?php while($sec_q->have_posts()): $sec_q->the_post(); ?>
                    <a href="<?php the_permalink(); ?>" style="display:block; width:100%; aspect-ratio:16/9; overflow:hidden; border-radius:4px; margin-bottom:15px;">
                        <?php 
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('medium', array('style' => 'width:100%; height:100%; object-fit:cover; transition:transform 0.5s ease;', 'onmouseover' => "this.style.transform='scale(1.05)'", 'onmouseout' => "this.style.transform='scale(1)'"));
                        } else {
                            echo '<div style="width:100%; height:100%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:0.8rem; color:#94a3b8; font-family:var(--font-ui);">Sin Foto</div>';
                        }
                        ?>
                    </a>
                    <h4 style="font-size:1.15rem; margin:0 0 10px 0; line-height:1.4;"><a href="<?php the_permalink(); ?>" style="color:var(--color-text); text-decoration:none;"><?php echo wp_trim_words(get_the_title(), 12, '...'); ?></a></h4>
                    <div style="font-size:0.8rem; color:var(--color-text-muted); margin-top:auto; font-family:var(--font-ui);"><?php echo get_the_date(); ?></div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    <?php endif; 
endforeach;
$sec_content = ob_get_clean();

if ( !empty(trim($sec_content)) ) :
?>
<section class="wapo-category-section secondary-zone">
    <h2 class="wapo-section-title"><span>Más Secciones</span></h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px;">
        <?php echo $sec_content; ?>
    </div>
</section>
<?php endif; ?>
