<?php
/*
	Plugin Name: Formulario de encuesta
	Plugin URI: http://www.ide-solution.com
	Description: Formulario de encuesta para recoger datos de los usuarios
	Author: David vilca
	Version: 0.1
	Author URI: http://www.vilcatec.com
*/


// Cuando el plugin se active se crea la tabla para recoger los datos si no existe
register_activation_hook(__FILE__, 'Kfp_Aspirante_init');
/**
 * Crea la tabla para recoger los datos del formulario
 *
 * @return void
 */
function Kfp_Aspirante_init() 
{
    global $wpdb; // Este objeto global permite acceder a la base de datos de WP
    // Crea la tabla sólo si no existe
    // Utiliza el mismo prefijo del resto de tablas
    $tabla_encuesta = $wpdb->prefix . 'cuestionario_encuesta1';
    $tabla_pregunta = $wpdb->prefix . 'cuestionario_pregunta1';
    $tabla_alternativa = $wpdb->prefix . 'cuestionario_alternativa1';
    $tabla_respuestas = $wpdb->prefix . 'cuestionario_respuestas1';
    $tabla_usuario = $wpdb->prefix . 'cuestionario_usuario1';
    // Utiliza el mismo tipo de orden de la base de datos
    $charset_collate = $wpdb->get_charset_collate();
    // Prepara la consulta
    $query1 = "CREATE TABLE IF NOT EXISTS $tabla_encuesta (
        enc_id mediumint(9) NOT NULL AUTO_INCREMENT,
        enc_nombre varchar(200) NOT NULL,
        enc_descripcion varchar(500) NOT NULL,
        enc_num_pregunta smallint(4) NOT NULL,
        enc_fecha_creacion datetime NOT NULL,
        enc_estado smallint(4) NOT NULL,     
        UNIQUE (enc_id)
        ) $charset_collate;";
    $query2 = "CREATE TABLE IF NOT EXISTS $tabla_pregunta (
        pre_id mediumint(9) NOT NULL AUTO_INCREMENT,
        pre_nombre varchar(500) NOT NULL,   
        enc_id mediumint(9) NOT NULL,      
        UNIQUE (pre_id)
        ) $charset_collate;";
    $query3 = "CREATE TABLE IF NOT EXISTS $tabla_alternativa (
        alt_id mediumint(9) NOT NULL AUTO_INCREMENT,
        alt_nombre varchar(500) NOT NULL,   
        pre_id mediumint(9) NOT NULL,      
        UNIQUE (alt_id)
        ) $charset_collate;";
    $query4 = "CREATE TABLE IF NOT EXISTS $tabla_respuestas (
        res_id mediumint(9) NOT NULL AUTO_INCREMENT,
        res_valor smallint(4) NOT NULL,   
        alt_id mediumint(9) NOT NULL,      
        UNIQUE (res_id)
        ) $charset_collate;";
    $query5 = "CREATE TABLE IF NOT EXISTS $tabla_usuario (
        usu_id mediumint(9) NOT NULL AUTO_INCREMENT,
        usu_correo varchar(100) NULL,   
        enc_id mediumint(9) NOT NULL,      
        UNIQUE (usu_id)
        ) $charset_collate;";        
    // La función dbDelta permite crear tablas de manera segura se
    // define en el archivo upgrade.php que se incluye a continuación
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($query1); // Lanza la consulta para crear la tabla de manera segura
    dbDelta($query2); // Lanza la consulta para crear la tabla de manera segura
    dbDelta($query3); // Lanza la consulta para crear la tabla de manera segura
    dbDelta($query4); // Lanza la consulta para crear la tabla de manera segura
    dbDelta($query5); // Lanza la consulta para crear la tabla de manera segura
}


// Define el shortcode y lo asocia a una función
add_shortcode('kfp_aspirante_form', 'Kfp_Aspirante_form');

function Kfp_Aspirante_form() 
{
    global $wpdb;
    if (!empty($_POST) 		
		AND wp_verify_nonce($_POST['aspirante_nonce'], 'graba_aspirante')
    ) {
    $tabla_cuestionario_pregunta = $wpdb->prefix . 'cuestionario_pregunta';
    $preguntas = $wpdb->get_results("SELECT * FROM $tabla_cuestionario_pregunta");
        foreach ( $preguntas as $pregunta ) {  
            $id = esc_textarea($pregunta->pre_id);
            $tabla_cuestionario_respuestas = $wpdb->prefix . 'cuestionario_respuestas'; 
            $obtenerdatos = $_POST['valor'.$id];   
            $valor = substr($obtenerdatos,0,1);     
            $alter = substr($obtenerdatos,2); 
            $wpdb->insert(
                $tabla_cuestionario_respuestas,
                array(
                    'res_valor' => $valor,
                    'alt_id' => $alter,                
                )
            );           
        }
        echo "<p class='exito'><b>Tus datos han sido registrados</b>. Gracias 
            por tu interés. En breve te compartiremos los resultados.<p>";
    }

	// Carga esta hoja de estilo para poner más bonito el formulario
    wp_enqueue_style('css_aspirante', plugins_url('style.css', __FILE__));
 
    // Esta función de PHP activa el almacenamiento en búfer de salida (output buffer)
    // Cuando termine el formulario lo imprime con la función ob_get_clean
    ob_start();

?>

    <form action="<?php get_the_permalink(); ?>" method="post" id="form_aspirante" class="cuestionario">
	<?php wp_nonce_field('graba_aspirante', 'aspirante_nonce'); ?>
    
<?php
    global $wpdb;
    $tabla_cuestionario_pregunta = $wpdb->prefix . 'cuestionario_pregunta';
    $preguntas = $wpdb->get_results("SELECT * FROM $tabla_cuestionario_pregunta");
    foreach ( $preguntas as $pregunta ) {
        $nombre = esc_textarea($pregunta->pre_nombre);
        $id = esc_textarea($pregunta->pre_id);
        echo "<div class='form-input'>
        <label for='nivel_html'>$nombre</label>";

        $tabla_cuestionario_alternativa = $wpdb->prefix . 'cuestionario_alternativa';
        $alternativas = $wpdb->get_results("SELECT * FROM $tabla_cuestionario_alternativa where pre_id=$id" );
        $incremento = 1;
        $idAlternativa;
        foreach ( $alternativas as $alternativa ) {
            $nombreAl = esc_textarea($alternativa->alt_nombre);
            $idAl = esc_textarea($alternativa->alt_id);
            echo "<br><input type='radio' name='valor$id' value='$incremento.$idAl' required> $nombreAl";
            $incremento= $incremento+1;
            $idAlternativa = $idAl;
        }    
        echo "</div>";    
    }
?>
        <div class="form-input">
            <input type="submit" value="Enviar">
        </div>
    </form>
<?php     
    // Devuelve el contenido del buffer de salida
    return ob_get_clean();
}

// El hook "admin_menu" permite agregar un nuevo item al menú de administración
add_action("admin_menu", "Kfp_Aspirante_menu");
/**
 * Agrega el menú del plugin al escritorio de WordPress
 *
 * @return void
 */
function Kfp_Aspirante_menu() 
{
    add_menu_page(
        'Formulario Encuestas', 'Encuestas', 'manage_options', 
        'kfp_aspirante_menu', 'Kfp_Aspirante_admin', 'dashicons-feedback', 75
    );
}

/**
 * Crea el contenido del panel de administración para el plugin
 *
 * @return void
 */
function Kfp_Aspirante_admin()
{
    global $wpdb;
    $tabla_encuesta = $wpdb->prefix . 'cuestionario_encuesta';
    echo '<!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
      Launch demo modal
    </button>
    
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            ...
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>';


    echo '<div class="wrap">
    <h1 class="wp-heading-inline">Encuestas</h1>
    <a
      href="http://localhost/wordpress/wp-admin/post-new.php?post_type=page"
      class="page-title-action"
      >Agregar nueva</a
    >
    <hr class="wp-header-end" />
  
    <h2 class="screen-reader-text">Lista de páginas filtradas</h2>
    <ul class="subsubsub">
      <li class="all">
        <a href="edit.php?post_type=page" class="current" aria-current="page"
          >Todas <span class="count">(4)</span></a
        >
        |
      </li>
      <li class="publish">
        <a href="edit.php?post_status=publish&amp;post_type=page"
          >Publicadas <span class="count">(3)</span></a
        >
        |
      </li>
      <li class="draft">
        <a href="edit.php?post_status=draft&amp;post_type=page"
          >Borrador <span class="count">(1)</span></a
        >
      </li>
    </ul>
  
    <form id="posts-filter" method="get">
      <p class="search-box">
        <label class="screen-reader-text" for="post-search-input"
          >Buscar páginas:</label
        >
        <input type="search" id="post-search-input" name="s" value="" />
        <input
          type="submit"
          id="search-submit"
          class="button"
          value="Buscar páginas"
        />
      </p>
      <input
        type="hidden"
        name="post_status"
        class="post_status_page"
        value="all"
      />
      <input type="hidden" name="post_type" class="post_type_page" value="page" />
      <input type="hidden" id="_wpnonce" name="_wpnonce" value="0be5147f5f" />
      <input
        type="hidden"
        name="_wp_http_referer"
        value="/wordpress/wp-admin/edit.php?post_type=page"
      />   
      <h2 class="screen-reader-text">Lista de páginas</h2>
      <table class="wp-list-table widefat fixed striped table-view-list pages">
        <thead>
          <tr>
            <th
              scope="col"
              id="title"
              class="manage-column column-title column-primary sortable desc"
            >
              <a
                href="http://localhost/wordpress/wp-admin/edit.php?post_type=page&amp;orderby=title&amp;order=asc"
              >
                <span>Título</span>
                <span class="sorting-indicator"></span>
              </a>
            </th>
            <th scope="col" id="author" class="manage-column">
              Descripción
            </th>
            <th
              scope="col"
              id="date"
              class="manage-column column-date sortable asc"
            >
              <a
                href="http://localhost/wordpress/wp-admin/edit.php?post_type=page&amp;orderby=date&amp;order=desc"
              >
                <span>Fecha de creación</span>
                <span class="sorting-indicator"></span>
              </a>
            </th>
            <th
              scope="col"
              id="comments"
              class="manage-column column-comments num sortable desc"
            >
              <a
                href="http://localhost/wordpress/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"
              >
                <span>Estado</span>
                <span class="sorting-indicator"></span
              ></a>
            </th>
          </tr>
        </thead>';
  
        echo '<tbody id="the-list">';
        
        $aspirantes = $wpdb->get_results("SELECT * FROM $tabla_encuesta");
        foreach ( $aspirantes as $aspirante ) {
            $enc_id = (int)$aspirante->enc_id;
            $nombre = esc_textarea($aspirante->enc_nombre);
            $descripcion = esc_textarea($aspirante->enc_descripcion);
            $fecha = esc_textarea($aspirante->enc_fecha_creacion);
            $estado = (int)$aspirante->enc_estado;   
            echo "<tr id='post-12' class='iedit author-self level-0 post-12 type-page status-publish hentry entry' >
            <td
            class='title column-title has-row-actions column-primary page-title'
            data-colname='Título'
          >
            <div class='locked-info'>
              <span class='locked-avatar'></span>
              <span class='locked-text'></span>
            </div>
            <strong>
              <a
                class='row-title'
                href='http://localhost/wordpress/wp-admin/post.php?post=12&amp;action=edit'
                aria-label='“cuestionario” (Editar)'
                >$nombre</a
              ></strong
            >        
            <div class='row-actions'>
              <span class='edit'>
                <a
                  href='http://localhost/wordpress/wp-admin/post.php?post=12&amp;action=edit'
                  aria-label='Editar “cuestionario”'
                  >Editar</a
                >
                |
              </span>
             <span class='trash'>
                <a
                  href='http://localhost/wordpress/wp-admin/post.php?post=12&amp;action=trash&amp;_wpnonce=c80c6f9030'
                  class='submitdelete'
                  aria-label='Mover “cuestionario” a la Papelera'
                  >Papelera</a
                >
                |
              </span>
              <span class='view'>
                <a
                  href='http://localhost/wordpress/cuestionario/'
                  rel='bookmark'
                  aria-label='Ver “cuestionario”'
                  >Ver</a
                >
              </span>
            </div>       
          </td>       
          <td class='author' data-colname='Autor'>
                  $descripcion
                </td>    
                <td class='date column-date' data-colname='Fecha'>
                  Publicado<br />$fecha
                </td>       
                <td class='comments column-comments' data-colname='Comentarios'>
                  <div class='post-com-count-wrapper'>
                    <span aria-hidden='true'>$estado</span>
                    <span class='screen-reader-text'>No hay comentarios</span>
                    <span
                      class='post-com-count post-com-count-pending post-com-count-no-pending'
                    >
                      <span
                        class='comment-count comment-count-no-pending'
                        aria-hidden='true'
                        >0</span
                      >
                      <span class='screen-reader-text'>No hay comentarios</span>
                    </span>
                  </div>
                </td>
             
                </tr>";
        }

        echo '</tbody>';

        echo'<tfoot>
          <tr>
            <th
              scope="col"
              class="manage-column column-title column-primary sortable desc"
            >
              <a
                href="http://localhost/wordpress/wp-admin/edit.php?post_type=page&amp;orderby=title&amp;order=asc"
                ><span>Título</span><span class="sorting-indicator"></span
              ></a>
            </th>
            <th scope="col" class="manage-column ">Descripción</th>         
            <th scope="col" class="manage-column column-date sortable asc">
              <a
                href="http://localhost/wordpress/wp-admin/edit.php?post_type=page&amp;orderby=date&amp;order=desc"
                ><span>Fecha de creación</span><span class="sorting-indicator"></span
              ></a>
            </th>
            <th
            scope="col"
            class="manage-column column-comments num sortable desc"
          >
            <a
              href="http://localhost/wordpress/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"
              ><span>Estado</span><span class="sorting-indicator"></span
            ></a>
          </th>
          </tr>
        </tfoot>
      </table>
    </form>
  </div>
  ';




}