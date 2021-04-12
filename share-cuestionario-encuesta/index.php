<?php
/*
	Plugin Name: Formulario de encuesta
	Plugin URI: http://www.ide-solution.com
	Description: Formulario de encuesta para recoger datos de los usuarios
	Author: David vilca
	Version: 2.1
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
        enc_nombre_mostrar smallint(4) NOT NULL, 
        enc_descripcion varchar(500) NOT NULL,
        enc_num_pregunta smallint(4) NOT NULL,
        enc_fecha_creacion datetime default CURRENT_TIMESTAMP NOT NULL,
        enc_correo varchar(50) NOT NULL,
        enc_clave varchar(50) NOT NULL,
        enc_asunto varchar(200) NOT NULL,
        enc_mensaje varchar(500) NOT NULL, 
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
        alt_color varchar(20) NOT NULL, 
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
        $tabla_cues_encu= $wpdb->prefix . 'cuestionario_encuesta';
        $resultencuesta = $wpdb->get_row("SELECT * FROM $tabla_cues_encu where enc_id=$idencuesta");
        $tabla_cuestionario_pregunta = $wpdb->prefix . 'cuestionario_pregunta';
        $preguntas = $wpdb->get_results("SELECT * FROM $tabla_cuestionario_pregunta where enc_id=$idencuesta");
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
                    'alt_id' => $alter
                )
            );           
        }
        $tabla_cuestionario_usuario = $wpdb->prefix . 'cuestionario_usuario';
        $correo = $_POST['correo']; 
        $idenc = $_POST['idEncuesta']; 
        $wpdb->insert(
            $tabla_cuestionario_usuario,
            array(
                'usu_correo' => $correo,
                'enc_id' => $idenc
            )
        );  
        

        // envio al correo

        if($_POST['correo']<>''){
            include("sendemail.php");

            //$mail_username="david_199180@hotmail.com";//Correo electronico saliente ejemplo: tucorreo@gmail.com
            $mail_username = $resultencuesta->enc_correo;
            $mail_userpassword=$resultencuesta->enc_clave;//Tu contraseña de gmail
            $mail_addAddress=$_POST['correo']; //correo electronico que recibira el mensaje
			//Ruta de la plantilla HTML para enviar nuestro mensaje
            $email_message = "".$resultencuesta->enc_mensaje."\n\n";      
            $email_message .= "<br>";


            //INICIA
         
            global $wpdb;
                $tabla_cuestionario_pregunta = $wpdb->prefix . 'cuestionario_pregunta';
                $preguntas = $wpdb->get_results("SELECT * FROM $tabla_cuestionario_pregunta where enc_id=$idencuesta");
                $num_pregunta =1 ;
                foreach ( $preguntas as $pregunta ) {
                    $nombre = esc_textarea($pregunta->pre_nombre);
                    $id = esc_textarea($pregunta->pre_id);  
                    $email_message .= "<div class='form-input'>
                    <label for='nivel_html' style='margin-bottom: 20px;'>Pregunta $num_pregunta: $nombre</label>";

                    $tabla_cuestionario_alternativa = $wpdb->prefix . 'cuestionario_alternativa';
                    $tabla_cuestionario_respuesta = $wpdb->prefix . 'cuestionario_respuestas';
                    $totalrespuestas = $wpdb->get_var("SELECT count(cr.res_valor) as cant from $tabla_cuestionario_alternativa as ca left join $tabla_cuestionario_respuesta as cr on ca.alt_id=cr.alt_id where pre_id=$id ");
                    $alternativas = $wpdb->get_results("SELECT ca.alt_id,ca.alt_nombre,ca.alt_color, count(cr.res_valor) as cant from $tabla_cuestionario_alternativa as ca left join $tabla_cuestionario_respuesta as cr on ca.alt_id=cr.alt_id where pre_id=$id group by ca.alt_id,ca.alt_nombre,ca.alt_color");
                    $incremento = 1;
                    $idAlternativa;
                    foreach ( $alternativas as $alternativa ) {
                        $nombreAl = esc_textarea($alternativa->alt_nombre);
                        $idAl = esc_textarea($alternativa->alt_id);
                        $colo = esc_textarea($alternativa->alt_color);                       
                        $cant = esc_textarea($alternativa->cant);  
               
                        $email_message .= '<div style="width: 100%; background-color: green;">                       
                        <div style="width: 100%; background-color: white; float: left; display: inline;">                                    
                                <div style="float: left; margin-left: 10px;"><div>'.number_format(($cant/$totalrespuestas*100),2,".",",").'%</div></div>                                                                                           
                                <div style="float: left; margin-left: 10px;"><div  style="width: 20px; height: 20px; border: 1px solid black; margin: 0.2em; border-radius: 25%;background: '.$colo.'"></div></div>
                                <div style="float: left; margin-left: 10px;"><div>'.$nombreAl.'</div></div>                                    
                            </div>
                        </div>';
                        $incremento= $incremento+1;
                        $idAlternativa = $idAl;
                    }   
                    $num_pregunta++;                     
                } 
            //FIN
        
        
           /*Inicio captura de datos enviados por $_POST para enviar el correo */
				$mail_setFromEmail=$resultencuesta->enc_correo;
				//$mail_setFromName="david";
				$txt_message=$resultencuesta->enc_mensaje;
				$mail_subject=$resultencuesta->enc_asunto;
				
				sendemail($mail_username,$mail_userpassword,$mail_setFromEmail,$mail_setFromName,$mail_addAddress,$txt_message,$mail_subject,$email_message);//Enviar el mensaje
                           
        }else{        
            echo "<p class='exito'><b>Tus respuestas han sido registradas</b>. Gracias por tu participación.<p>";
        }
    }

	// Carga esta hoja de estilo para poner más bonito el formulario
    wp_enqueue_style('css_aspirante', plugins_url('style.css', __FILE__));
    wp_enqueue_style('css_asp', plugins_url('estilos.css', __FILE__));
    wp_enqueue_script('js_asp', plugins_url('popup.js', __FILE__));


 
    // Esta función de PHP activa el almacenamiento en búfer de salida (output buffer)
    // Cuando termine el formulario lo imprime con la función ob_get_clean
    ob_start();
    // valida si ya se ha generado un registro de la encuesta
    if(empty($_POST) ){
?>   
    <form action="<?php get_the_permalink(); ?>" method="post" id="form_aspirante" class="cuestionario">
	<?php wp_nonce_field('graba_aspirante', 'aspirante_nonce'); ?>
    
<?php
    global $wpdb;
    $tabla_cuestionario_encuesta = $wpdb->prefix . 'cuestionario_encuesta';
    $resultadoencuesta = $wpdb->get_row("SELECT * FROM $tabla_cuestionario_encuesta where enc_id=$idencuesta and enc_estado=1");        
    if($resultadoencuesta->enc_nombre_mostrar==1){
    echo "<h2>$resultadoencuesta->enc_nombre</h2>";
    }
    if($resultadoencuesta->enc_estado<>0){
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
                echo "<input type='radio' name='valor$id' value='$incremento.$idAl' required> $nombreAl <br>";
                $incremento= $incremento+1;
                $idAlternativa = $idAl;
            }    
            echo "</div>";    
        }
        echo "<div class='contenedor'>
        <article>          
            <button type='button' id='btn-abrir-popup' class='btn-abrir-popup'>Registar Encuesta</button>    
        </article>    
        <div class='overlay' id='overlay'>
            <div class='popup' id='popup'>
                <!--<a href='#' id='btn-cerrar-popup' class='btn-cerrar-popup'  ><i class='fas fa-times'></i>X</a>-->
                <button type='submit' class='btn-cerrar-popup'  ><i class='fas fa-times'></i>X</button> 
                <h3>REGISTRA TU CORREO</h3>
                <h4>y recibe información de la encuesta</h4>             
                    <div class='contenedor-inputs'>   
                        <input type='hidden' name='idEncuesta' value='".$idencuesta."'>                 
                        <input type='email' name='correo' placeholder='Correo'>
                    </div>         
                    <input type='submit' value='Enviar'>          
            </div>
        </div>
    </div>";
    }else{
        echo "<center><p>Encuesta inhabilitada</p></center>";
    }
    
?>        
    </form>
<?php     
    }
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
    $aspirantes = $wpdb->get_results("SELECT enc_id, enc_nombre,enc_descripcion, enc_num_pregunta, enc_fecha_creacion, IF(enc_estado=1,'activo','inactivo') as enc_estado FROM $tabla_encuesta");
    $aspirantestotal = $wpdb->get_var("SELECT count(*) FROM $tabla_encuesta");
    $aspirantesactivo = $wpdb->get_var("SELECT count(*) FROM $tabla_encuesta where enc_estado=1");
    $cantidadtotal = $aspirantestotal;
    $cantidadpublic = $aspirantesactivo;
    $cantidadinactivas = $aspirantestotal - $aspirantesactivo;

   
    if (!empty($_POST) 		
		AND wp_verify_nonce($_POST['encuesta_nonce'], 'graba_encuesta')
        AND $_POST['titulo']<>''
    ) {
        $tabla_cuestionario_encuesta1 = $wpdb->prefix . 'cuestionario_encuesta';
        $encuestas= $wpdb->get_results("SELECT * FROM $tabla_cuestionario_encuesta1");
        
        $enc_titulo = $_POST['titulo']; 
        $enc_titulo_mostrar = $_POST['mostrar_titulo']; 
        if($enc_titulo_mostrar==null){
            $enc_titulo_mostrar=0;
        }
        $enc_descripcion = $_POST['descripcion']; 
        $enc_cantpre = $_POST['cant_pre']; 
        $enc_estado = $_POST['estado']; 
        if($enc_estado==null){
            $enc_estado=0;
        }
        $enc_correo = $_POST['correo']; 
        $enc_clave = $_POST['clave'];
        $enc_asunto = $_POST['asunto']; 
        $enc_mensaje = $_POST['mensaje']; 
        $wpdb->insert(
            $tabla_cuestionario_encuesta1,
            array(
                'enc_nombre' => $enc_titulo,
                'enc_descripcion' => $enc_descripcion,  
                'enc_num_pregunta' => $enc_cantpre,                
                'enc_estado'=>$enc_estado,
                'enc_nombre_mostrar' =>$enc_titulo_mostrar,
                'enc_correo' => $enc_correo,
                'enc_clave' => $enc_clave,
                'enc_asunto' => $enc_asunto,
                'enc_mensaje' => $enc_mensaje
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
                $alt_color = $_POST['color'.$cantidadinc.$cantidadalt]; 
                if($alt_nombre<>''){
                    $wpdb->insert(
                        $tabla_cuestionario_alternativa1,
                        array(
                            'alt_nombre' => $alt_nombre,  
                            'alt_color' => $alt_color,                        
                            'pre_id'=>$pre_id_1
                        )
                    ); 
                }                
                $cantidadalt++;
            }  
            $cantidadinc++;
        }           
            
            echo "<p class='exito'><b>La encuesta fue registrada con éxito</b><p>";
    }
    else if (!empty($_POST) 		
    AND wp_verify_nonce($_POST['encuesta_nonce'], 'graba_encuesta_editar')
    AND $_POST['titulo']<>''
) {         
        $tabla_cuestionario_encuesta1 = $wpdb->prefix . 'cuestionario_encuesta';
        $enc_id = $_POST['id_encuesta'];
        $enc_titulo = $_POST['titulo']; 
        $enc_titulo_mostrar = $_POST['mostrar_titulo']; 
        if($enc_titulo_mostrar==null){
            $enc_titulo_mostrar=0;
        }
        $enc_descripcion = $_POST['descripcion']; 
        $enc_cantpre = $_POST['cant_pre']; 
        $enc_estado = $_POST['estado']; 
        if($enc_estado==null){
            $enc_estado=0;
        }
        $enc_correo = $_POST['correo']; 
        $enc_clave = $_POST['clave']; 
        $enc_asunto = $_POST['asunto']; 
        $enc_mensaje = $_POST['mensaje']; 
        $wpdb->update(
            $tabla_cuestionario_encuesta1,
            array(
                'enc_nombre' => $enc_titulo,
                'enc_descripcion' => $enc_descripcion,  
                'enc_num_pregunta' => $enc_cantpre,                
                'enc_estado'=>$enc_estado,
                'enc_nombre_mostrar' =>$enc_titulo_mostrar,                                
                'enc_correo' => $enc_correo,
                'enc_clave' => $enc_clave,
                'enc_asunto' => $enc_asunto,
                'enc_mensaje' => $enc_mensaje
            ),
            array( 'enc_id' => $enc_id ), 
        );  

}

    	// Carga esta hoja de estilo para poner más bonito el formulario
        wp_enqueue_style('css_aspirante', plugins_url('estilos.css', __FILE__));
        wp_enqueue_script('js_aspirante', plugins_url('popup.js', __FILE__));
        wp_enqueue_script('js_script', plugins_url('script.js', __FILE__));
        wp_enqueue_style('css_semaforo', plugins_url('semaforo.css', __FILE__));
        
  echo '<div class="wrap">
        <h1 class="wp-heading-inline">Encuestas</h1>
        <a href="#" id="btn-abrir-popup" class="page-title-action" >Agregar nueva</a>   
        <div class="overlay" id="overlay">
            <div class="popup" id="popup">
                <a href="#" id="btn-cerrar-popup" class="btn-cerrar-popup"><i class="fas fa-times"></i>X</a>                   
                <div class="wrap">'; 
                    ?>        
                    <form action="<?php get_the_permalink(); ?>" method="post" name="createuser" id="createuser" class="validate" novalidate="novalidate">
                    <?php  wp_nonce_field('graba_encuesta', 'encuesta_nonce'); ?>

                        <?php
                        echo '<h1 id="add-new-user">Crear nueva encuesta</h1>
                        <h2 id="add-new-user">Datos de la encuesta</h2>';
                        echo '<input name="action" type="hidden" value="createuser" />
                        <input type="hidden" id="_wpnonce_create-user" name="_wpnonce_create-user" value="ec2e348e19" />
                        <input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/user-new.php" />
                        <table class="form-table" role="presentation" id="dynamic_field">
                            <tbody>
                                <tr class="form-field form-required">
                                    <th scope="row">
                                        <label for="user_login">Titulo
                                        <span class="description">(requerido)</span></label>
                                    </th>
                                    <td>
                                        <input name="titulo" type="text" id="user_login" value="" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" required="required" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Mostrar titulo</th>
                                    <td>
                                        <input type="checkbox" name="mostrar_titulo" id="send_user_notification" value="1"
                                            checked="checked" />
                                        <label for="send_user_notification">Activar</label>
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
                                        <label for="send_user_notification">Activar</label>
                                    </td>
                                </tr>                 
                            </tbody>
                        </table>
                        <h2 id="add-new-user">Configuración para envío de correos</h2>
                        <table class="form-table" role="presentation" id="dynamic_field">
                            <tbody>
                                <tr class="form-field form-required">
                                    <th scope="row">
                                        <label for="correo">Correo <span class="description">(requerido) <br>Correo permitido hotmail</span></label>
                                    </th>
                                    <td>
                                        <input name="correo" type="email" id="correo" value="" required="required" />
                                    </td>
                                </tr> 
                                <tr class="form-field form-required">
                                    <th scope="row">
                                        <label for="clave">Clave <span class="description">(requerido)</span></label>
                                    </th>
                                    <td>
                                        <input name="clave" type="password" id="clave" value="" required="required" />
                                    </td>
                                </tr>                                
                                <tr class="form-field form-required">
                                    <th scope="row">
                                        <label for="asunto">Asunto <span class="description">(requerido)</span></label>
                                    </th>
                                    <td><input name="asunto" type="text" id="asunto" value="" required="required" /></td>
                                </tr>
                                <tr class="form-field form-required">
                                    <th scope="row">
                                        <label for="mensaje">Mensaje</label>
                                    </th>
                                    <td><input name="mensaje" type="text" id="mensaje" value="" /></td>
                                </tr>                                      
                            </tbody>
                        </table>';
                        wp_enqueue_script('js_jquery', plugins_url('jquery.js', __FILE__));                        
                        wp_enqueue_script('js_bootstrap', plugins_url('bootstrap.js', __FILE__));
                        wp_enqueue_script('js_dinamic', plugins_url('dinamic.js', __FILE__));
                        echo '<p class="submit">
                            <input type="submit" name="createuser" id="createusersub" class="button button-primary" value="Crear nueva encuesta" />
                            </p>
                    </form> 
                </div>
            </div>
        </div>';
  echo '<hr class="wp-header-end" />  
        <h2 class="screen-reader-text">Lista de páginas filtradas</h2>
        <ul class="subsubsub">
            <li class="all"><a href="" class="current" aria-current="page" >Todas <span class="count">('.$cantidadtotal.')</span></a>|</li>
            <li class="publish"><a href="">Publicadas <span class="count">('.$cantidadpublic.')</span></a>|</li>
            <li class="draft"><a href="">Inactivas <span class="count">('.$cantidadinactivas.')</span></a></li>
        </ul>
 
            <h2 class="screen-reader-text">Lista de páginas</h2>
            <table class="wp-list-table widefat fixed striped table-view-list pages">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-title column-primary sortable desc">
                            <a href=""><span>Título</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column ">Descripción</th>         
                        <th scope="col" class="manage-column column-date sortable asc">
                            <a href="" ><span>Fecha de creación</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col"  id="comments" class="manage-column column-date  sortable desc">
                            <a href="" ><span>Shortcode</span><span class="sorting-indicator"></span ></a>
                        </th>
                        <th scope="col" class="manage-column column-comments num sortable desc">
                            <a href="" ><span>Estado</span><span class="sorting-indicator"></span></a>
                        </th>
                    </tr>
                </thead>';
                wp_enqueue_script('js_aspirante1', plugins_url('popup1.js', __FILE__));
                wp_enqueue_script('js_aspirante11', plugins_url('popup.js', __FILE__));                         
                echo '<tbody id="the-list">';                
                foreach ( $aspirantes as $aspirante ) {
                    $enc_id = (int)$aspirante->enc_id;
                    $nombre = esc_textarea($aspirante->enc_nombre);
                    $descripcion = esc_textarea($aspirante->enc_descripcion);
                    $fecha = esc_textarea($aspirante->enc_fecha_creacion);
                    $estado = $aspirante->enc_estado; 
                    $estadoEnvio = '1';
                    $textoestado= 'activar';
                    if($estado=='activo'){
                        $estadoEnvio='0';
                        $textoestado= 'inhabilitar';
                    }  
                    echo "<tr id='post-12' class='iedit author-self level-0 post-12 type-page status-publish hentry entry' >
                        <td class='title column-title has-row-actions column-primary page-title' data-colname='Título'>           
                            <strong>
                                <a class='row-title' href='' aria-label='“cuestionario” (Editar)'>$nombre</a>
                            </strong>        
                            <div class='row-actions'>
                                <span class='edit'><a href='#' id='btn-abrir-popup-edith-$enc_id'>Editar</a>|</span>
                                <span class='trash'><input type='hidden' value='$estadoEnvio' id='valorenvio'/><a href='#more-$enc_id' class='submitdelete' >$textoestado</a>|</span>
                                <span class='view'><a href='#' id='btn-abrir-popup-ver-$enc_id'>Ver</a></span>
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
                            <span aria-hidden='true'>$estado</span>
                        </td>        
                    </tr>";


                    echo '<div class="overlay" id="overlay-ver-'.$enc_id.'">
            <div class="popup" id="popup-ver-'.$enc_id.'">
                <a href="#" id="btn-cerrar-popup-ver-'.$enc_id.'" class="btn-cerrar-popup-ver"><i class="fas fa-times"></i>X</a>                   
                ';
 
                global $wpdb;
                $tabla_cuestionario_pregunta = $wpdb->prefix . 'cuestionario_pregunta';
                $preguntas = $wpdb->get_results("SELECT * FROM $tabla_cuestionario_pregunta where enc_id=$enc_id");
                $num_pregunta =1 ;
                foreach ( $preguntas as $pregunta ) {
                    $nombre = esc_textarea($pregunta->pre_nombre);
                    $id = esc_textarea($pregunta->pre_id);  
                    echo "<div class='form-input'>
                    <label for='nivel_html' class='pupver'>Pregunta $num_pregunta: $nombre</label>";

                    $tabla_cuestionario_alternativa = $wpdb->prefix . 'cuestionario_alternativa';
                    $tabla_cuestionario_respuesta = $wpdb->prefix . 'cuestionario_respuestas';
                    $totalrespuestas = $wpdb->get_var("SELECT count(cr.res_valor) as cant from $tabla_cuestionario_alternativa as ca left join $tabla_cuestionario_respuesta as cr on ca.alt_id=cr.alt_id where pre_id=$id ");
                    $alternativas = $wpdb->get_results("SELECT ca.alt_id,ca.alt_nombre,ca.alt_color, count(cr.res_valor) as cant from $tabla_cuestionario_alternativa as ca left join $tabla_cuestionario_respuesta as cr on ca.alt_id=cr.alt_id where pre_id=$id group by ca.alt_id,ca.alt_nombre,ca.alt_color");
                    $incremento = 1;
                    $idAlternativa;
                    foreach ( $alternativas as $alternativa ) {
                        $nombreAl = esc_textarea($alternativa->alt_nombre);
                        $idAl = esc_textarea($alternativa->alt_id);
                        $colo = esc_textarea($alternativa->alt_color);                       
                        $cant = esc_textarea($alternativa->cant);  
               
                        echo '<div id="contenedor-principal">                       
                            <div id="contenedor">                                    
                                    <div class="formu"><div>'.number_format(($cant/$totalrespuestas*100),2,".",",").'%</div></div>                                                                                           
                                    <div class="formu"><div class="div-semaforo" style="background: '.$colo.'"></div></div>
                                    <div class="formu"><div>'.$nombreAl.'</div></div>                                    
                                </div>
                            </div>';
                        $incremento= $incremento+1;
                        $idAlternativa = $idAl;
                    }   
                    $num_pregunta++; 
                    echo "</br></div>";    
                }     
            echo '</div>
        </div>';


        echo '<div class="overlay" id="overlay-edith-'.$enc_id.'">
        <div class="popup" id="popup-edith-'.$enc_id.'">
            <a href="#" id="btn-cerrar-popup-edith-'.$enc_id.'" class="btn-cerrar-popup-ver"><i class="fas fa-times"></i>X</a>                   
            <div class="wrap">'; 
            global $wpdb;
                $tabla_cues_encuesta = $wpdb->prefix . 'cuestionario_encuesta';
                $resultencuesta = $wpdb->get_row("SELECT * FROM $tabla_cues_encuesta where enc_id=$enc_id");
                $mostrarValor = '';
                if(($resultencuesta->enc_nombre_mostrar)=='1'){
                    $mostrarValor = 'checked="checked"';
                }
                $mostrarestado = '';
                if(($resultencuesta->enc_estado)=='1'){
                    $mostrarestado = 'checked="checked"';
                }
            ?>        
            <form action="<?php get_the_permalink(); ?>" method="post">
            <?php  wp_nonce_field('graba_encuesta_editar', 'encuesta_nonce'); ?>         
                <?php
                echo '<input name="id_encuesta" type="hidden" value="'.$enc_id.'"   />';
                echo '<h1 id="add-new-user">Editar encuesta</h1>
                <h2 id="add-new-user">Editar la encuesta</h2>
                <input name="mov" type="hidden" value="editar" />';
                echo '
                                <div  class="contenedor2">
                                <div class="formu2"><label for="user_login">Titulo <span class="description">(requerido)</span></label></div>
                                <div class="formu2"><input name="titulo" type="text" id="user_login" value="'.$resultencuesta->enc_nombre.'" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" required="required" /></div>
                                </div>
                                <div  class="contenedor2">
                                <div class="formu2">Mostrar titulo</div>
                                <div class="formu2"><input type="checkbox" name="mostrar_titulo" id="send_user_notification" value="1" '.$mostrarValor.' /><label for="send_user_notification">Activar</label></div>
                                </div>
                                <div class="contenedor2">
                                <div class="formu2"><label for="email">Descripcion <span class="description">(requerido)</span></label></div>
                                <div class="formu2"><input name="descripcion" type="text" id="email" value="'.$resultencuesta->enc_descripcion.'" /></div>
                                </div>
                                <div class="contenedor2">
                                <div class="formu2"><label for="first_name">N° de preguntas </label></div>
                                <div class="formu2"><input name="cant_pre" type="number" id="cant" value="'.$resultencuesta->enc_num_pregunta.'" required="required" /></div>
                                <div class="formu2"><button type="button" name="add" id="add" class="button button-primary">Add </button></div>
                                </div>
                                <div class="contenedor2">
                                <div class="formu2">Estado de la encuesta</div>
                                <div class="formu2"><input type="checkbox" name="estado" id="send_user_notification" value="1" '.$mostrarestado.' /><label for="send_user_notification">Activar</label></div>
                                </div>
                <h2 id="add-new-user">Configuración para envío de correos</h2>        
                                <div class="contenedor2">   
                                <div class="formu2"><label for="correo">Correo <span class="description">(requerido) <br>Correo permitido hotmail</span></label></div>
                                <div class="formu2"><input name="correo" type="email" id="correo" value="'.$resultencuesta->enc_correo.'" required="required" /></div>
                                </div>
                                <div class="contenedor2">   
                                <div class="formu2"><label for="clave">Clave <span class="description">(requerido)</span></label></div>
                                <div class="formu2"><input name="clave" type="password" id="clave" value="'.$resultencuesta->enc_clave.'" required="required" /></div>
                                </div>
                                <div class="contenedor2">
                                <div class="formu2"><label for="asunto">Asunto <span class="description">(requerido)</span></label></div>
                                <div class="formu2"><input name="asunto" type="text" id="asunto" value="'.$resultencuesta->enc_asunto.'" required="required" /></div>
                                </div>
                                <div class="contenedor2">
                                <div class="formu2"><label for="mensaje">Mensaje</label></div>
                                <div class="formu2"><input name="mensaje" type="text" id="mensaje" value="'.$resultencuesta->enc_mensaje.'" /></div>
                                </div>
                       ';
                wp_enqueue_script('js_jquery', plugins_url('jquery.js', __FILE__));
                wp_enqueue_script('js_popper', plugins_url('popper.js', __FILE__));             
                wp_enqueue_script('js_dinamic', plugins_url('dinamic.js', __FILE__));
                echo '<p class="submit">
                    <input type="submit" name="createuser" id="createusersub" class="button button-primary" value="Editar encuesta" />
                    </p>
            </form> 
            </div>
        </div>
    </div>';



                }                
                echo '</tbody>';

                echo'<tfoot>
                    <tr>
                        <th scope="col" class="manage-column column-title column-primary sortable desc">
                            <a href=""><span>Título</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column ">Descripción</th>         
                        <th scope="col" class="manage-column column-date sortable asc">
                            <a href="" ><span>Fecha de creación</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col"  id="comments" class="manage-column column-date  sortable desc">
                            <a href="" ><span>Shortcode</span><span class="sorting-indicator"></span ></a>
                        </th>
                        <th scope="col" class="manage-column column-comments num sortable desc">
                            <a href="" ><span>Estado</span><span class="sorting-indicator"></span></a>
                        </th>
                    </tr>
                </tfoot>
            </table>';

        
                echo '
    </div>
  ';
}



//Insertar Javascript js y enviar ruta admin-ajax.php
add_action('wp_enqueue_scripts', 'dcms_insertar_js');

function dcms_insertar_js(){

	if (!is_home()) return;

	wp_register_script('dcms_miscript', get_template_directory_uri(). '/script.js', array('jquery'), '1', true );
	wp_enqueue_script('dcms_miscript');

	wp_localize_script('dcms_miscript','dcms_vars',['ajaxurl'=>admin_url('admin-ajax.php')]);
}

//Devolver datos a archivo js
add_action('wp_ajax_nopriv_dcms_ajax_readmore','dcms_enviar_contenido');
add_action('wp_ajax_dcms_ajax_readmore','dcms_enviar_contenido');

function dcms_enviar_contenido()
{
    global $wpdb;
    $tabla_encuestaUdpate = $wpdb->prefix . 'cuestionario_encuesta';
	$id_post = absint($_POST['id_post']);
    $estad = absint($_POST['estado']);
	

    $result = $wpdb->update(
        $tabla_encuestaUdpate,
        array(        
            'enc_estado' => $estad
        ),
        array( 'enc_id' => $id_post )
    );

	//sleep(2);
	
	echo $id_post;

	wp_die();
}