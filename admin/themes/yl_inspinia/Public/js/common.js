$(function(){
    var ajaxForm_list = $('form.js-ajax-form');
    if (ajaxForm_list.length) {


        ajaxForm_list.on('submit', function (e) {
            e.preventDefault();
            var btn = $(this).find(".js-ajax-submit"),
                form = $(this);
            if(btn.data("loading")){
                return;
            }
            //ie处理placeholder提交问题
            if (!$.support.leadingWhitespace) {
                form.find('[placeholder]').each(function () {
                    var input = $(this);
                    if (input.val() == input.attr('placeholder')) {
                        input.val('');
                    }
                });
            }
            form.ajaxSubmit({
                url: btn.data('action') ? btn.data('action') : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                dataType: 'json',
                beforeSubmit: function (arr, $form, options) {
                    btn.data("loading",true);
                    var text = btn.text();
                    //按钮文案、状态修改
                    btn.text(text + '中...').prop('disabled', true).addClass('disabled');
                },
                success: function (data, statusText, xhr, $form) {
                    var text = btn.text();
                    //按钮文案、状态修改
                    btn.removeClass('disabled').text(text.replace('中...', ''));
                    if (data.state === 'success') {
                        layer.msg(data.info, {
                            icon: 1,
                            time: 2000 //2秒关闭（如果不配置，默认是3秒）
                        }, function(){
                            if(data.url){
                                location.href=data.url;
                            }else{
                                history.back();
                            }
                        });
                    } else if (data.state === 'fail') {
                        btn.prop('disabled',false).removeClass('disabled');
                        layer.msg(data.info, {
                            icon: 0,
                            time: 2000 //2秒关闭（如果不配置，默认是3秒）
                        });
                    }
                },
                complete: function(){
                    btn.data("loading",false);
                }
            });
        });
    }
});