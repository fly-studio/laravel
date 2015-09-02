<div class="form-group">
	<label for="title">父级</label>
	<select name="pid" id="pid" class="form-control">
		<option value="0">总分类</option>
		<{foreach $_tree as $item}>
		<option value="<{$item->getKey()}>" <{if $item->getKey() == $_data.id}>disabled="disabled" style="color:gray"<{/if}><{if $item->getKey() == $_data.pid}>selected="selected"<{/if}> ><{$item.title|indent:$item.level:'├'}></option>
		<{/foreach}>
	</select>
</div>
<div class="form-group">
	<label for="title">标题</label>
	<input type="text" class="form-control" name="title" id="title" placeholder="请输入标题..." value="<{$_data.title|default:old('title')}>">
</div>
<div class="form-group">
	<label for="content">内容</label>
	<div id="editormd">
		<textarea name="content" id="content" class="form-control" style="display:none;"><{$_data.content|default:old('content')}></textarea>
	</div>
</div>
<button type="submit" class="btn btn-default">保存</button>
