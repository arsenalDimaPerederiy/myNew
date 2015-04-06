$(document).ready(function ()
{
    setTimeout(function () { $('.alert-success, .alert-danger').fadeOut(); }, 2000);

    $('.ajax_remove').click(function()
    {
        var that = $(this),
            url = that.attr('href');

        $.ajax
        ({
            url: url,
            dataType: 'json',
            beforeSend: function () {
                that.removeClass('icon-trash');
                that.addClass('icon-refresh');
            },
            success: function(data)
            {
                if(data == 'ok')
                    that.closest('.row').remove();
                else
                    alert('Удалить запись не удалось');
            },
            error: function ()
            {
                that.removeClass('icon-refresh');
                that.addClass('icon-remove');
            }
        });

        return false;
    });

    $('.check_all').click(function ()
    {
        if($(this).is(':checked'))
            $('.check_link').prop('checked', true);
        else
            $('.check_link').prop('checked', false);
    });

    $('.delete_all').click(function(){

        var url = $(this).attr('href'),
            ids = '';

        $('input.check_link:checked').each(function ()
        {
            if( ! $(this).hasClass('disabled'))
                ids += $(this).val()+',';
        });

        if(ids)
        {
             $.ajax({
                url: url+ids,
                type: "GET",
                dataType: 'json',
                success: function(data)
                {
                    if(data == 'ok')
                        $('input.check_link:checked').each(function () {
                            $(this).closest('.row').remove();
                        });
                    else
                        alert('Удалить записи не удалось');
                 },
             });
        }

        return false
    });

    $('.show_pre').click(function ()
    {
        $(this).next().toggle();
        return false;
    });

    $('.close').click(function ()
    {
        var that = $(this),
            class_name = that.attr('data-dismiss');

        that.closest('.'+class_name).hide();
    });

    $('.disabled').click(function ()
    {
        return false;
    });

    $('#set_page_size').click(function ()
    {
        document.cookie = "ps="+$(this).val();

        window.location.href = window.location.href;
    });

    $('.toggle').click(function ()
    {
        var that = $(this);
        that.closest('tbody.row').find('.row').toggle();

        if(that.hasClass('icon-minus-sign'))
        {
            that.removeClass('icon-minus-sign');
            that.addClass('icon-plus-sign');
        }
        else
        {
            that.removeClass('icon-plus-sign');
            that.addClass('icon-minus-sign');
        }

        return false;
    });

    $("#reset_filter").click(function ()
    {
        window.location.href = $(this).closest('form').attr('action');
    });

    $('.show_edit_box').click(function ()
    {
        var url = $(this).attr('data-url');

        $('#edit_url_box input[name=url]').val(url);
        $('#edit_url_box input[name=old_url]').val(url);
        $('#edit_url_box').modal('show');

        return false;
    });

    $('.ajax_form').submit(function ()
    {
        var form = $(this),
            old_url = form.find('input[name=old_url]').val(),
            url = form.find('input[name=url]').val();

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            dataType: "json",
            data: form.serializeArray(),
            success: function(data)
            {
                $('a[href="'+old_url+'"]').text(url).attr('href', url);
                $('.show_edit_box[data-url="'+old_url+'"]').attr('data-url', url)
                $('#edit_url_box').modal('hide');
            },
        });

        return false;
    });
});
