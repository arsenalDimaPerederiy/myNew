<h3>Редактироване привязок ссылок</h3>

<?php if($urls): ?>
    <div class="right">
        Выводить по
        <select name="page_size" class="input-small" id="set_page_size">
            <?php foreach(array(100,200,500,1000) as $size): ?>
                <option value="<?=$size?>" <?php if($size==$page_size): ?>selected<?php endif; ?>><?=$size?></option>
            <?php endforeach; ?>
        </select>
        ссылок
    </div>
    <form action="<?=$this->buildUrl('edit')?>">
        <div class="input-append">
            <input type="hidden" name="action" value="edit"/>
            <input type="text" name="filter" value="<?=$filter?>" placeholder="Фраза/URL для фильтрации" />
            <input type="submit" class="btn btn-primary" value="Отфильтровать"/>
            <input type="button" class="btn" id="reset_filter" value="Сбросить"/>
        </div>
    </form>
    <div class="right">
        <strong>Показано привязок: <?=$urls['count']?> из <?=$urls['all']?>, страница <?=$page?></strong>
    </div>
    <div class="left btn-toolbar table_navigations">
        <div class="btn-group">
            <?php $class1 = null;  $class2 = null; ?>
            <?php if($page == 1) $class1 = 'disabled';  ?>
            <?php if($urls['count'] != $page_size) $class2 = 'disabled';  ?>

            <a class="btn <?=$class1?>" title="Предыдущая страница" href="<?=$this->buildUrl('edit')?>&page=<?=$page-1?>"><i class="icon-chevron-left"></i></a>
            <a class="btn <?=$class2?>" title="Следующая страница" href="<?=$this->buildUrl('edit')?>&page=<?=$page+1?>"><i class="icon-chevron-right"></i></a>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th width="10"><input type="checkbox" class="check_all"/></th>
                <th width="150"><a href="<?=$this->buildUrl('delete')?>&type=links&ids=" class="btn btn-danger btn-mini delete_all">Удалить</a></th>
                <th>Анкор</th>
                <th>URL акцептора</th>
            </tr>
        </thead>
        <?php foreach($urls['links'] as $url => $links): ?>
            <tbody class="row">
            <tr>
                <td><input type="checkbox" class="check_link" value="<?=implode(',', array_keys($links))?>"/></td>
                <th colspan="2">
                    <a href="#" class="toggle icon-minus-sign"></a>
                    <a href="<?=$this->buildUrl('delete')?>&ids=<?=implode(',',array_keys($links))?>&type=links" class="icon-trash ajax_remove"></a>
                    <a target="_blank" class="search_field" href="<?=$url?>"><?=$url?></a>
                </th>
                <td></td>
            </tr>

            <?php if(!empty($links)): ?>
                <?php foreach($links as $id => $link): ?>
                    <tr class="row">
                        <td><?=$id?></td>
                        <td>
                            <a href="<?=$this->buildUrl('delete')?>&ids=<?=$id?>&type=links" class="icon-trash ajax_remove"></a>
                            <span class="search_field"><?=$link['phrase']?></span>
                        </td>
                        <td>
                            <a class="search_field" target="_blank" href="<?=$link['url']?>"><?=$link['url']?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        <?php endforeach; ?>
        <thead>
            <tr>
                <th><input type="checkbox" class="check_all"/></th>
                <th width="150"><a href="<?=$this->buildUrl('delete')?>&type=links&ids=" class="btn btn-danger btn-mini delete_all">Удалить</a></th>
                <th></th>
                <th></th>
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
<?php else: ?>
<span class="text-info">Еще нет сохраненных привязок</span>
<?php endif; ?>
