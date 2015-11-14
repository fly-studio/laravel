<{extends file="extends/main.block.tpl"}>

<{block "head-title"}><title>选择微信公众号</title><{/block}>
<{block "head-styles-plus"}>
<style>
#tools {}
#tools li {line-height: 300%}
</style>
<{/block}>


<{block "body-container"}>
<div class="container" role="main">
	<div class="page-header">
		<h1>选择公众号</h1>
	</div>
	<ul id="tools">
	<{foreach $_accounts as $v}>
		<li><a href="<{'wechat/choose_query'|url}>?id=<{$v->getKey()}>&url=<{$_url}>"><i class="glyphicon glyphicon-list"> </i> <{$v->name}> (<small><{$v->account}></small>) </a></li>
	<{/foreach}>
	</ul>
</div>
<{/block}>
