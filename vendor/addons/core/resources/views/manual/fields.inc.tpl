<div class="form-group">
	<label for="title">父级</label>
	<select name="pid" id="pid" class="form-control">
		
	</select>
</div>
<div class="form-group">
	<label for="title">标题</label>
	<input type="text" class="form-control" name="title" id="title" placeholder="请输入标题..." value="<{$_data->content|default:old('title')}>">
</div>
<div class="form-group">
	<label for="content">内容</label>
	<div id="editormd">
		<textarea name="content" id="content" class="form-control" style="display:none;"><{$_data->content|default:old('content')}></textarea>
	</div>
</div>
<button type="submit" class="btn btn-default">保存</button>
