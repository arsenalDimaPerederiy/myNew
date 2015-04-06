<h3>Настройки приложения</h3>


<?php if($post): ?>
    <div class="alert alert-success">
        Данные успешно сохранены
    </div>
<?php endif; ?>

<form class="form-horizontal" action="<?=$this->buildUrl('settings')?>" method="post">
    <div class="control-group">
        <label class="control-label">Кодировка выводимых ссылок:</label>
        <div class="controls">
            <select name="encoding">
                <?php foreach($encodings as $encoding): ?>
                    <option value="<?=$encoding?>" <?php if($encoding == $prefered_encoding): ?>selected<?php endif; ?>><?=$encoding?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Количество выводимых ссылок:</label>
        <div class="controls">
            <select name="num_links" title="Рекомендуем выводить от 1 до 3 ссылок.">
                <?php for($i=1; $i<=5; $i++): ?>
                    <option value="<?=$i?>" <?php if($i == $num): ?>selected<?php endif; ?>><?=$i?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Разделитель ссылок:</label>
        <div class="controls">
            <select name="delimeter">
                <?php foreach($delimeters as $delimeter => $name): ?>
                    <option value="<?=$delimeter?>" <?php if($delimeter == $prefered_delimeter): ?>selected<?php endif; ?>><?=$name?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Заголовок блока ссылок:</label>
        <div class="controls">
            <input type="text" name="title" value="<?=htmlspecialchars($title)?>"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Игнорируемые GET параметры (перечислить через ","):</label>
        <div class="controls">
            <textarea name="get_params" rows="5"><?=$get_params?></textarea>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
          <input type="submit" class="btn btn-primary ajax_submit" value="Сохранить" />
        </div>
  </div>
</form>
