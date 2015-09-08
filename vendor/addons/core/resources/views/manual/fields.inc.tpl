<div class="form-group">
	<label for="title">父级</label>
	<select name="pid" id="pid" class="form-control">
		<option value="0">总分类</option>
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
<div class="form-group text-center">
	<button type="submit" class="btn btn-info">保存</button>
</div>
<script>
(function($){
$().ready(function(){
	var tree = <{$_tree|json_encode nofilter}>;
	var recursive = function(items, prefix) {
		prefix = typeof prefix == 'undefined' ? '' : prefix;
		var html = '';
		for(var i = 0; i < items.length; ++i) {
			var v = items[i];
			html += '<option value="'+ v.id +'" '+ (parseInt('<{$_data.id}>') == parseInt(v.id) ? 'disabled="disabled" style="color:gray"' : '') + (parseInt('<{$_data.pid}>') == parseInt(v.id) ? 'selected="selected"' : '') + '>' + prefix + ( i == items.length - 1 ? '└' : '├') + v.title + '</option>';
			if (v['children'])
				html += recursive(v['children'], prefix + (i == items.length - 1 ? '&nbsp;&nbsp;' : '│'));
		}
		return html;
	}

	if (tree[0])
	{
		var html = recursive(tree[0]['children']);
		$('#pid').append(html);
	}
});
})(jQuery);
</script>