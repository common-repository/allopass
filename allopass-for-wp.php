<?php
/*
Plugin Name: Allopass for WP
Plugin URI: http://allopass.writoo.com/
Description: English (<strong>Plugin Free</strong>) Want to monetize the content you create for your blog?
Français (Plugin gratuit) Allopass qui vous permettra de monétiser votre site. 
Version: 1.0.7
Author: HP Developpement / Writoo 
Author URI:  http://allopass.writoo.com
License: GPLv2 or later
*/


define("URL_ALLOPASS",plugin_dir_url( __FILE__ ));
define("DIR_ALLOPASS",dirname( __FILE__ ) ."/");
add_action('admin_menu', 'wp_allopass' );
if (!is_home()) add_filter('the_content', 'allopass_content',100);
register_activation_hook( __FILE__, 'allopass_init' );
register_deactivation_hook(__FILE__, 'allopass_deactivation');

/*********************** Suppression du programme ****************/
function allopass_deactivation() 
{
	$del["del"]=getenv("HTTP_HOST");
	allopass_post($del);
}

/*********************** Activation du programme ****************/
function allopass_init() 
{
	$add[]="";
	allopass_post($add);
}



// ---------------------------------------------------

function allopass_content($content) {
	$page= get_permalink($post->ID); 
    	$param = get_post_meta( 2, 'param',true);
	$expli = get_post_meta( 2, 'expli',true);

	$r = $content;
	$q = explode('[allopass]',$r);
	if (count($q)>1)
	{
		$r  = $q[0] ."<br><div style='width:295px;color:#000000'><b>" . nl2br(get_post_meta( 2, "expli", true)) . "</b></div><br>";
		$r .= file_get_contents(URL_ALLOPASS . "allopass1.php?page=$page&param=$param&expli=$expli");
	}
		
	if (isset($_GET["err"])) $r =  "<div style='color:#FF0000;background-color:#FFFF00;width:295px;text-align:center;padding:5px;border:solid 1px #000000'><b>Code erroné / Erroneous code</b></div><br>" .  $r;
	else
	if (isset($_GET["ok"]))
	{
		if (file_get_contents("http://www.writoo.com/verif.php?code=" . $_GET["ok"]) ==1)
		$r= str_replace("[allopass]","",$content);
	}


	return $r ;
}


function wp_allopass() 
{
	add_options_page('Allopass', 'Allopass' , 'manage_options', 'Allopass_admin', 'Allopass_options');
}

function allopass_options() {
	if (!isset($_GET["lang"]))
	{
		$lang = get_post_meta( 2, "lang", true);
		if ($lang=="") $lang="fr" ;

		require_once(DIR_ALLOPASS . "$lang.php");
		$lang="fr";
	}
	else
	{
		require_once(DIR_ALLOPASS . $_GET["lang"] . ".php");
		$lang =  $_GET["lang"] ;
		delete_post_meta( 2, "lang");
		add_post_meta( 2, "lang", $lang);
	}

	delete_post_meta( 2, "err");
	foreach($_GET["err"] as $key=>$value)
	add_post_meta( 2, "err", "Code erroné");	

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$param = "";
	$expli = "";
	if (isset($_POST))
	{
		if (isset($_POST["param"]))
		{
			delete_post_meta( 2, "param");
			add_post_meta( 2, "param", trim($_POST["param"]));	
		}
		if (isset($_POST["expli"]))
		{
			delete_post_meta( 2, "expli");
			add_post_meta( 2, "expli", trim($_POST["expli"]));	
		}

	}
	$param = get_post_meta( 2, "param", true);
	$expli = get_post_meta( 2, "expli", true);
	if (trim($expli)=="") $expli = CETTEPAGE ;

?>
<br>
<div style='float:right'>
<a href='?page=<?php echo $_GET["page"]?>&lang=fr'><img src='<?php echo URL_ALLOPASS ?>fr.jpg' border=0></a> 
<a href='?page=<?php echo $_GET["page"]?>&lang=us'><img src='<?php echo URL_ALLOPASS ?>us.jpg' border=0></a>
</div>
<h1><?php echo CONFIG ?></h1>
<form method=post>
<?php foreach($_GET as $key=>$value) echo "<input type=hidden name=$key value=$value>";?>
<table>
<tr>
<td valign=top><img src='<?php echo URL_ALLOPASS ?>allopass.png' style='width:390px;height:319px'></td>
<td valign=top>
<h2><?php echo EXPLI ?></h2>
<?php echo EXPLIQUEZ ?><br>
<textarea name=expli style='width:100%;height:60px;color:#123456;text-weight: bolder;background-color:#D0D0D0'><?php echo $expli?></textarea>

<br>
<div style='border-right:solid 1px #D0D0D0;padding:5px;margin-left:2px'>
<h2><?php echo CONFIGURATION ?></h2>
<?php echo IDENTIFIANT ?><br>
<input type=text name=param value="<?php echo $param ?>" style='color:#123456;text-weight: bolder;background-color:#D0D0D0'><br><br>
<?php echo CLIQUEZ ?> <br>
<a href="http://fr.allopass.com/advert?from=sponsorship&target=1605852" target="_blank"><img border="0" src="http://www.allopass.com/imgweb/fr/pub/17030.gif" alt="Allopass"></a><br>
<center><input type=submit value="<?php echo VALIDER ?>"></center>
</div>
</tr>
<tr><td colspan=2>
<div style='background-color:#D7D5DA;padding:3px'>
<center><?php echo PROTEGE ?><center>
</div>
<i><?php echo PLUGIN ?></i>
</td></tr>
</table>

</form>
<?php
}

function allopass_post($datas)
{
	$datas["host"] = getenv("HTTP_HOST");

	$options = array(
   	'http'		=> array(
	'timeout' 	=> 5, 
     	'method'	=> "POST",
     	'header'	=>	"Accept-language: fr\r\n".
       		"Content-type: application/x-www-form-urlencoded\r\n",
     	'content'	=>http_build_query($datas)
 	));
 
	$context = stream_context_create($options);
 
	$fh = fopen( 'http://www.writoo.com/scriptallo.php', 'r', false, $context);
 	// Récupération des meta informations du flux
 	$meta = stream_get_meta_data( $fh );
 	// Récupération des headers sous forme de tableau
 	$headers= $meta['wrapper_data'];
 	// Récupération de la réponse du serveur
 	$retour= '';
 	
	while( !feof( $fh ) ) $retour  .= fread( $fh, 1024 );

 	fclose( $fh );
 
	echo  $retour;

}
?>
