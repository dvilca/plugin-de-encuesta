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
    $tabla_encuesta = $wpdb->prefix . 'cuestionario_encuesta';
    $tabla_pregunta = $wpdb->prefix . 'cuestionario_pregunta';
    $tabla_alternativa = $wpdb->prefix . 'cuestionario_alternativa';
    $tabla_respuestas = $wpdb->prefix . 'cuestionario_respuestas';
    $tabla_usuario = $wpdb->prefix . 'cuestionario_usuario';
    // Utiliza el mismo tipo de orden de la base de datos
    $charset_collate = $wpdb->get_charset_collate();
    // Prepara la consulta
    $query1 = "CREATE TABLE IF NOT EXISTS $tabla_encuesta (
        enc_id mediumint(9) NOT NULL AUTO_INCREMENT,
        enc_nombre varchar(200) NOT NULL,
        enc_descripcion varchar(500) NOT NULL,
        enc_num_pregunta smallint(4) NOT NULL,
        enc_fecha_creacion datetime default CURRENT_TIMESTAMP NOT NULL,     
        enc_estado smallint(4) NOT NULL,     
        PRIMARY KEY (enc_id)
        ) $charset_collate;";
    $query2 = "CREATE TABLE IF NOT EXISTS $tabla_pregunta (
        pre_id mediumint(9) NOT NULL AUTO_INCREMENT,
        pre_nombre varchar(500) NOT NULL,   
        enc_id mediumint(9) NOT NULL,      
        PRIMARY KEY (pre_id)
        ) $charset_collate;";
    $query3 = "CREATE TABLE IF NOT EXISTS $tabla_alternativa (
        alt_id mediumint(9) NOT NULL AUTO_INCREMENT,
        alt_nombre varchar(500) NOT NULL,   
        pre_id mediumint(9) NOT NULL,      
        PRIMARY KEY (alt_id)
        ) $charset_collate;";
    $query4 = "CREATE TABLE IF NOT EXISTS $tabla_respuestas (
        res_id mediumint(9) NOT NULL AUTO_INCREMENT,
        res_valor smallint(4) NOT NULL,   
        alt_id mediumint(9) NOT NULL,      
        PRIMARY KEY (res_id)
        ) $charset_collate;";
    $query5 = "CREATE TABLE IF NOT EXISTS $tabla_usuario (
        usu_id mediumint(9) NOT NULL AUTO_INCREMENT,
        usu_correo varchar(100) NULL,   
        enc_id mediumint(9) NOT NULL,      
        PRIMARY KEY (usu_id)
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

function Kfp_Aspirante_form($atts) 
{

    $args = shortcode_atts(array('id'=>'1'),$atts);

    $idencuesta=$args["id"];

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
    $tabla_cuestionario_encuesta = $wpdb->prefix . 'cuestionario_encuesta';
    $tituloEncuesta = $wpdb->get_var("SELECT enc_nombre FROM $tabla_cuestionario_encuesta where enc_id=$idencuesta and enc_estado=1");
    echo "<h2>$tituloEncuesta</h2>";

    if($tituloEncuesta<>''){
        $tabla_cuestionario_pregunta = $wpdb->prefix . 'cuestionario_pregunta';
        $preguntas = $wpdb->get_results("SELECT * FROM $tabla_cuestionario_pregunta where enc_id=$idencuesta ");
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
        echo '<div class="form-input">
            <input type="submit" value="Enviar">
        </div>';
    }else{
        echo "<center><p>Encuesta inhabilitada</p></center>";
    }

?>        
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
    $aspirantes = $wpdb->get_results("SELECT enc_id, enc_nombre,enc_descripcion, enc_num_pregunta, enc_fecha_creacion, IF(enc_estado=1,'activo','boqueado') as enc_estado FROM $tabla_encuesta");
    $aspirantestotal = $wpdb->get_var("SELECT count(*) FROM $tabla_encuesta");
    $aspirantesactivo = $wpdb->get_var("SELECT count(*) FROM $tabla_encuesta where enc_estado=1");
    $cantidadtotal = $aspirantestotal;
    $cantidadpublic = $aspirantesactivo;
    $cantidadinactivas = $aspirantestotal - $aspirantesactivo;

    // insercion de tablas
    if (!empty($_POST) 		
		AND wp_verify_nonce($_POST['encuesta_nonce'], 'graba_encuesta')
        AND $_POST['titulo']<>''
    ) {
        $tabla_cuestionario_encuesta1 = $wpdb->prefix . 'cuestionario_encuesta';
        $encuestas= $wpdb->get_results("SELECT * FROM $tabla_cuestionario_encuesta1");
        
        $enc_titulo = $_POST['titulo']; 
        $enc_descripcion = $_POST['descripcion']; 
        $enc_cantpre = $_POST['cant_pre']; 
        $enc_estado = $_POST['estado']; 

        $wpdb->insert(
            $tabla_cuestionario_encuesta1,
            array(
                'enc_nombre' => $enc_titulo,
                'enc_descripcion' => $enc_descripcion,  
                'enc_num_pregunta' => $enc_cantpre,                
                'enc_estado'=>$enc_estado
            )
        );  
   
        $enc_id_1 = $wpdb->get_var("SELECT max(enc_id) FROM $tabla_cuestionario_encuesta1");

        $tabla_cuestionario_pregunta1 = $wpdb->prefix . 'cuestionario_pregunta';
        $tabla_cuestionario_alternativa1 = $wpdb->prefix . 'cuestionario_alternativa';
        $cantidadinc=1;
        while ($cantidadinc<= $enc_cantpre ) {
            $pre_nombre = $_POST['pre'.$cantidadinc]; 
            $wpdb->insert(
                $tabla_cuestionario_pregunta1,
                array(
                    'pre_nombre' => $pre_nombre,                          
                    'enc_id'=>$enc_id_1
                )
            ); 
            $pre_id_1 = $wpdb->get_var("SELECT max(pre_id) FROM $tabla_cuestionario_pregunta1");
            $cantidadalt=1;
            while ($cantidadalt<= 5) {
                $alt_nombre = $_POST['alt'.$cantidadinc.$cantidadalt]; 
                if($alt_nombre<>''){
                    $wpdb->insert(
                        $tabla_cuestionario_alternativa1,
                        array(
                            'alt_nombre' => $alt_nombre,                          
                            'pre_id'=>$pre_id_1
                        )
                    ); 
                }                
                $cantidadalt++;
            }  
            $cantidadinc++;
        }           

            echo "<p class='exito'><b>Tus datos han sido registrados</b>. Gracias  por tu interés. En breve te compartiremos los resultados.<p>";
    }






    	// Carga esta hoja de estilo para poner más bonito el formulario
        wp_enqueue_style('css_aspirante', plugins_url('estilos.css', __FILE__));
        wp_enqueue_script('js_aspirante', plugins_url('popup.js', __FILE__));
 
        
    echo '<div class="wrap">
    <h1 class="wp-heading-inline">Encuestas</h1>
    <a
      href="#" id="btn-abrir-popup"
      class="page-title-action"
      >Agregar nueva</a
    >   
    <div class="overlay" id="overlay">
        <div class="popup" id="popup">
            <a href="#" id="btn-cerrar-popup" class="btn-cerrar-popup"><i class="fas fa-times"></i>X</a>                   
            <div class="wrap">
    <h1 id="add-new-user">Crear nueva encuesta</h1>

    <div id="ajax-response"></div>'; 
    ?>

     
    <form action="<?php get_the_permalink(); ?>" method="post" name="createuser" id="createuser" class="validate" novalidate="novalidate">
    <?php  wp_nonce_field('graba_encuesta', 'encuesta_nonce'); ?>

        <?php
        echo '<input name="action" type="hidden" value="createuser" />
        <input type="hidden" id="_wpnonce_create-user" name="_wpnonce_create-user" value="ec2e348e19" /><input
            type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/user-new.php" />
        <table class="form-table" role="presentation" id="dynamic_field">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="user_login">Titulo
                            <span class="description">(requerido)</span></label>
                    </th>
                    <td>
                        <input name="titulo" type="text" id="user_login" value="" aria-required="true"
                            autocapitalize="none" autocorrect="off" maxlength="60" required="required" />
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="email">Descripcion <span class="description">(requerido)</span></label>
                    </th>
                    <td><input name="descripcion" type="text" id="email" value="" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">N° de preguntas </label></th>
                    <td>
                        <input name="cant_pre" type="number" id="cant" value="" required="required" />
                       <button type="button" name="add" id="add" class="button button-primary">Add </button>
                    </td>
                </tr>      
                <tr>
                    <th scope="row">Estado de la encuesta</th>
                    <td>
                        <input type="checkbox" name="estado" id="send_user_notification" value="1"
                            checked="checked" />
                        <label for="send_user_notification">Activado</label>
                    </td>
                </tr>                 
            </tbody>
        </table>';
        wp_enqueue_script('js_jquery', plugins_url('jquery.js', __FILE__));
        wp_enqueue_script('js_popper', plugins_url('popper.js', __FILE__));
        wp_enqueue_script('js_bootstrap', plugins_url('bootstrap.js', __FILE__));
        wp_enqueue_script('js_dinamic', plugins_url('dinamic.js', __FILE__));
        echo '<p class="submit">
            <input type="submit" name="createuser" id="createusersub" class="button button-primary"
                value="Crear nueva encuesta" />
        </p>
    </form> ';

echo '</div>
        </div>
    </div>
    <hr class="wp-header-end" />
  
    <h2 class="screen-reader-text">Lista de páginas filtradas</h2>
    <ul class="subsubsub">
      <li class="all">
        <a href="" class="current" aria-current="page"
          >Todas <span class="count">('.$cantidadtotal.')</span></a
        >
        |
      </li>
      <li class="publish">
        <a href=""
          >Publicadas <span class="count">('.$cantidadpublic.')</span></a
        >
        |
      </li>
      <li class="draft">
        <a href=""
          >Inactivas <span class="count">('.$cantidadinactivas.')</span></a
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
            <th scope="col" id="title"  class="manage-column column-title column-primary sortable desc" >
              <a  href="" >
                <span>Título</span>
                <span class="sorting-indicator"></span>
              </a>
            </th>
            <th scope="col" id="author" class="manage-column"> Descripción </th>
            <th scope="col" id="date" class="manage-column column-date sortable asc"  >
              <a  href="" >
                <span>Fecha de creación</span>
                <span class="sorting-indicator"></span>
              </a>
            </th>
            <th scope="col"  id="comments" class="manage-column column-date  sortable desc" >
              <a href="" >
                <span>Shortcode</span>
                <span class="sorting-indicator"></span >
                </a>
            </th>
            <th scope="col" id="comments" class="manage-column column-comments num sortable desc" >
              <a href="" >
                <span>Estado</span>
                <span class="sorting-indicator"></span >
                </a>
            </th>
          </tr>
        </thead>';
  
        echo '<tbody id="the-list">';
        
        
        foreach ( $aspirantes as $aspirante ) {
            $enc_id = (int)$aspirante->enc_id;
            $nombre = esc_textarea($aspirante->enc_nombre);
            $descripcion = esc_textarea($aspirante->enc_descripcion);
            $fecha = esc_textarea($aspirante->enc_fecha_creacion);
            $estado = $aspirante->enc_estado;   
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
                href=''
                aria-label='“cuestionario” (Editar)'
                >$nombre</a
              ></strong
            >        
            <div class='row-actions'>
              <span class='edit'>
                <a
                  href=''
                  aria-label='Editar “cuestionario”'
                  >Editar</a
                >
                |
              </span>
             <span class='trash'>
                <a
                  href=''
                  class='submitdelete'
                  aria-label='Mover “cuestionario” a la Papelera'
                  >Papelera</a
                >
                |
              </span>
              <span class='view'>
                <a
                  href=''
                  rel='bookmark'
                  aria-label='Ver “cuestionario”'
                  >Ver</a
                >
              </span>
            </div>       
          </td>       
          <td class='author' data-colname='Autor'>  $descripcion  </td>    
        <td class='date column-date' data-colname='Fecha'>
            Publicado<br />$fecha
        </td>  
        <td class='date column-date' data-colname='Fecha'>
            [kfp_aspirante_form id='$enc_id']
        </td>      
        <td class='comments column-comments' data-colname='Comentarios'>
            <div class='post-com-count-wrapper'>
            <span aria-hidden='true'>$estado</span>      
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
                href=""
                ><span>Título</span><span class="sorting-indicator"></span
              ></a>
            </th>
            <th scope="col" class="manage-column ">Descripción</th>         
            <th scope="col" class="manage-column column-date sortable asc">
              <a
                href=""
                ><span>Fecha de creación</span><span class="sorting-indicator"></span
              ></a>
            </th>
            <th scope="col"  id="comments" class="manage-column column-date  sortable desc" >
              <a href="" >
                <span>Shortcode</span>
                <span class="sorting-indicator"></span >
                </a>
            </th>
            <th
            scope="col"
            class="manage-column column-comments num sortable desc"
          >
            <a
              href=""
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