<!doctype html>
<head>
<meta charset="utf-8">
<title><?=$title;?></title>
<link href='http://fonts.googleapis.com/css?family=Arimo:400,700|Ubuntu:400,500,700|Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="<?=base_url();?>assets/css/bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="<?=base_url();?>assets/css/fanci-tablegrid.css"/>
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

-->
<script type="text/javascript" src="<?=base_url();?>assets/js/jquery-1.7.1.min.js"></script>
<!--[if (gte IE 6)&(lte IE 8)]>
  <script type="text/javascript" src="<?=base_url();?>assets/scripts/selectivizr.js"></script>
  <noscript><link rel="stylesheet" href="[fallback css]" /></noscript>
<![endif]-->
<script type="text/javascript" src="<?=base_url();?>assets/js/jquery-ui-1.8.16.min.js"></script>
<script type="text/javascript" src="<?=base_url();?>assets/js/jquery.fanCIgrid.js"></script>
<script type="text/javascript" src="<?=base_url();?>assets/js/jquery.history.js"></script>
<script type="text/javascript" src="<?=base_url();?>assets/js/jquery.tipsy.js"></script>

<?=$scripts?>
<style type="text/css">
	.toolbar-container {
		  display: table-cell;
      vertical-align: middle;
      position: relative;
	}
</style>
<body>
  	<div class="container">
  		<div class="toolbar">
  				<?="<h1>$caption</h1>"; ?>
  				<ul><li><?=isset( $toolbar ) ? $toolbar:''?></li></ul>

  		</div>
  		<div class="table-container">
  			<?=$contenido; ?>
  		</div>
  	</div>
</body>

</html>