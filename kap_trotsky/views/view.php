<h3>Загруженный КАП </h3>
<?php if($kap): ?>
    <div class="right">
        Выводить по
        <select name="page_size" class="input-small" id="set_page_size">
            <?php foreach(array(100,200,500,1000) as $size): ?>
                <option value="<?=$size?>" <?php if($size==$page_size): ?>selected<?php endif; ?>><?=$size?></option>
            <?php endforeach; ?>
        </select>
        ссылок
    </div>
    <form action="<?=$this->buildUrl('view')?>">
        <div class="input-append">
            <input type="hidden" name="action" value="view"/>
            <input type="text" name="filter" value="<?=$filter?>" placeholder="Фраза/URL для фильтрации" />
            <input type="submit" class="btn btn-primary" value="Отфильтровать"/>
            <input type="button" class="btn" id="reset_filter" value="Сбросить"/>
        </div>
    </form>
    <div class="right">
        <strong>Показано фраз: <?=$kap['count']?> из <?=$kap['all']?>, страница <?=$page?></strong>
    </div>
    <div class="left btn-toolbar table_navigations">
        <div class="btn-group">
            <?php $class1 = null;  $class1 = null; ?>
            <?php if($page == 1) $class1 = 'disabled';  ?>
            <?php if($kap['count'] != $page_size) $class2 = 'disabled';  ?>

            <a class="btn <?=$class1?>" title="Предыдущая страница" href="<?=$this->buildUrl('view')?>&page=<?=$page-1?>"><i class="icon-chevron-left"></i></a>
            <a class="btn <?=$class2?>" title="Следующая страница" href="<?=$this->buildUrl('view')?>&page=<?=$page+1?>"><i class="icon-chevron-right"></i></a>
        </div>
    </div>
    <div class="modal hide fade" id="edit_url_box">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Редактирование URL</h3>
      </div>
        <form action="<?=$this->buildUrl('edit_url')?>" method="post" accept-charset="utf-8" class="ajax_form">
          <div class="modal-body">
            <input class="input-xxlarge" type="text" name="url" value=""/>
            <input type="hidden" name="old_url" value=""/>
            <label>
                <input type="checkbox" name="change_links" value="1"/> Обновить привязки
            </label>
          </div>
          <div class="modal-footer">
            <a href="#" class="btn">Закрыть</a>
            <button href="#" type="submit" class="btn btn-primary">Сохранить</button>
          </div>
        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th width="10"><input type="checkbox" class="check_all"/></th>
                <th width="150"><a href="<?=$this->buildUrl('delete')?>&type=kap&ids=" class="btn btn-danger btn-mini delete_all">Удалить</a></th>
            </tr>
        </thead>
        <?php foreach($kap['kap'] as $url => $phrases): ?>
            <tbody class="row">
            <tr>
                <th width="1"><input type="checkbox" class="check_link" value="<?=implode(',',array_keys($phrases))?>"/></th>
                <th colspan="2">
                    <a href="#" class="toggle icon-minus-sign"></a>
                    <a href="<?=$this->buildUrl('delete')?>&ids=<?=implode(',',array_keys($phrases))?>&type=kap" class="icon-trash ajax_remove"></a>
                    <a target="_blank" href="<?=$url?>" class="search_field"><?=$url?></a>
                    <a href="<?=$this->buildUrl('edit_url')?>" data-url="<?=$url?>" class="icon-edit show_edit_box"></a>
                </th>
            </tr>
            <?php foreach($phrases as $id => $phrase): ?>
                <tr class="row">
                    <td></td>
                    <td style="padding-left:40px" >
                        <a href="<?=$this->buildUrl('delete')?>&ids=<?=$id?>&type=kap" class="icon-trash ajax_remove"></a>
                        <span class="search_field"><?=$phrase?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php endforeach; ?>
        <thead>
            <tr>
                <th width="10"><input type="checkbox" class="check_all"/></th>
                <th width="150"><a href="<?=$this->buildUrl('delete')?>&type=kap&ids=" class="btn btn-danger btn-mini delete_all">Удалить</a></th>
            </tr>
        </thead>
    </table>
    <div class="right">
        Выводить по
        <select name="page_size" class="input-small" id="set_page_size">
            <?php foreach(array(100,200,500,1000) as $size): ?>
                <option value="<?=$size?>" <?php if($size==$page_size): ?>selected<?php endif; ?>><?=$size?></option>
            <?php endforeach; ?>
        </select>
        ссылок
    </div>
<?php endif; ?>
