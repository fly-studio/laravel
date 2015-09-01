<!DOCTYPE html>
<html lang="en">
<head>
<{block "head"}>
	<meta charset="UTF-8">
	<{block "head-title"}><{include file="common/title.inc.tpl"}><{/block}>
	<meta name="csrf-token" content="<{csrf_token()}>">
	<{block "head-meta-responsive"}>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="renderer" content="webkit">
	<{/block}>
	<{block "head-meta-seo"}>
	<meta name="Keywords" content="" />
	<meta name="Description" content="" />
	<{/block}>
	<{block "head-icons"}>
	<{include file="common/icons.inc.tpl"}>
	<{/block}>
	<{block "head-styles"}>
		<{block "head-styles-before"}><{/block}>
		<{include file="common/styles.inc.tpl"}>
		<{block "head-styles-plus"}><{/block}>
		<{block "head-styles-after"}><{/block}>
	<{/block}>
	<{block "head-scripts"}>
		<{block "head-scripts-before"}><{/block}>
		<{include file="common/scripts.inc.tpl"}>
		<{block "head-scripts-validate"}><{include file="common/validate.inc.tpl"}><{/block}>
		<{block "head-scripts-plus"}><{/block}>
		<{block "head-scripts-after"}><{/block}>
	<{/block}>
	<{block "head-plus"}><{/block}>
<{/block}>
</head>
<body>
<{block "body-container"}>
<div class="container">
	
</div>
<{/block}>
<{block "body-scripts"}>
<script>
<{block "body-scripts-jquery"}>
	(function($){

	})(jQuery);
<{/block}>
</script>
<{/block}>
</body>
</html>