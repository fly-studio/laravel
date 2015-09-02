<{extends file="manual/edit.tpl"}>

<{block "body-container"}>
<div class="container">
	<h1 class="page-header">新增</h1>
	<form action="<{'manual'|url}>" id="form" method="POST" class="form-horizontal form-bordered">
		<{csrf_field() nofilter}>
		<{include file="manual/fields.inc.tpl"}>
	</form>
</div>
<{/block}>
