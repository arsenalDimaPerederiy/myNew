<h3>Загрузка нового КАПа</h3>


<?php if($upload): ?>
    <?php if( ! empty($excluded)): ?>
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h5>При загрузке капа были исключены следующие ссылки</h5>
            <ul>
                <?php foreach($excluded as $link): ?>
                    <li><?=$link?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            КАП успешно загружен
        </div>
    <?php endif; ?>
<?php endif; ?>

<form enctype="multipart/form-data" class="form-horizontal" action="<?=$this->buildUrl('upload')?>" method="POST">
    <h4>Введите КАП в поле</h4>
    <label>
        <textarea rows="10" style="width: 50em" name="kap"></textarea>
    </label>
    <h4>Или выберите файл</h4>
    <label>
        <input type="file" name="kap_file" class="btn"/>
    </label>
    <label>
        <input type="checkbox" name="reset" /> <strong>Удалить старый КАП</strong>
    </label>
    <label>
        <button type="submit" class="btn btn-primary">Загрузить</button>
    </label>
</form>
