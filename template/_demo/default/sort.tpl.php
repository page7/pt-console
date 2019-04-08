
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title">排序</h4>
</div>

<style>
#sort-items .sortable-placeholder { display:block; height:48px; border:#eee dashed 2px; border-radius:4px; margin:5px; }
#sort-items li { padding-left:32px; }
#sort-items li span { position:absolute; color:#ccc; top:10px; left:16px; }
</style>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" onsubmit="return false;">
    <ul id="sort-items" class="list-group">
        <?php foreach ($items as $v) { ?>
        <li class="list-group-item">
            <span class="glyphicon glyphicon-move"></span>
            <?php echo $v['name']; ?>
            <input type="hidden" name="item[]" value="<?php echo $v['id']; ?>" />
        </li>
        <?php } ?>
    </ul>

    <input type="hidden" name="pid" value="<?php echo $v['pid']; ?>" />
</form>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
    <button type="button" class="btn btn-primary btn-save">保存</button>
</div>


<script src="<?php echo RESOURCES_URL; ?>js/jquery.sortable.js"></script>

<script>
$(function(){
    $("#sort-items").sortable({items:"li"});
});
</script>

