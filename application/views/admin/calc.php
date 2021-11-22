<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header2.php');
?>
<div  class="alert alert-default" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="alert-heading">Результат подсчета</h4>
                        <p id="itog" >  </p> 
</div>
 
<section class="card">
    <div class="card-header">
        <span class="cat__core__title">
            <strong>Калькулятор</strong>
        </span>
    </div>
	<form id="form-user_login" class="login-form" action="javascript:void(null);" method="post" OnSubmit="ajax_post('change_trans',this,'#itog');">
             
    <div class="card-block">
    <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l13">Отдаю</label>
                            <div class="col-md-9">
                                <select  class="form-control" name="crypto"><option value="1">Криптовалюту</option><option value="0">Деньги</option></select>
                            </div>
		</div>
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
                    <!-- Horizontal Form -->
                    
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0">Комиссия на курс</label>
                            <div class="col-md-9">
                                
								<input type="text" name="rate"  class="form-control" value="<?=vars('rate')?>">
                            </div>
                        </div>
                    
                    <!-- End Horizontal Form -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
                    <!-- Horizontal Form -->
                    
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0">Наша комиссия</label>
                            <div class="col-md-9">
                                <input type="text"  class="form-control" name="btc_com" value="<?=vars('btc_com')?>">
                            </div>
                        </div>
                    
                    <!-- End Horizontal Form -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
                    <!-- Horizontal Form -->
                     
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0">Комисиия платежного метода</label>
                            <div class="col-md-9">
                               <input type="text"  class="form-control" name="pay_methods_com" value="0">
                            </div>
                        </div>
                     
                    <!-- End Horizontal Form -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
                    <!-- Horizontal Form -->
                    
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0">Комиссия крипты перевода</label>
                            <div class="col-md-9">
                                <input type="text"  class="form-control" name="valut_com" value="0">
                            </div>
                        </div>
                     
                    <!-- End Horizontal Form -->
                </div>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
                    <!-- Horizontal Form -->
                   
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0">Сумма отдаю</label>
                            <div class="col-md-9">
                               <input  class="form-control" type="text" name="sum" value="50">
                            </div>
                        </div>
                    
                    <!-- End Horizontal Form -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
                    <!-- Horizontal Form -->
                  
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0">Сумма получаю</label>
                            <div class="col-md-9">
                               <input  class="form-control" type="text" name="sum2" value="50">
                            </div>
                        </div>
                    
                    <!-- End Horizontal Form -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
                    <!-- Horizontal Form -->
                   
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0">Курс крипты</label>
                            <div class="col-md-9">
                               <input  class="form-control" type="text" name="kurs" value="2222">
                            </div>
                        </div>
                    
                    <!-- End Horizontal Form -->
                </div>
            </div>
        </div>
        <div class="form-actions">
                            <div class="form-group row">
                                <div class="col-md-9 offset-md-3">
                                    <button type="submit" class="btn btn-primary">Подсчитать</button>
                                </div>
                            </div>
        </div>
    </div>
	</form>
</section>

<!-- START: page scripts -->
<script>
    $(function() {

        ///////////////////////////////////////////////////
        // SIDEBAR CURRENT STATE
        $('.cat__apps__messaging__tab').on('click', function(){
            $('.cat__apps__messaging__tab').removeClass('cat__apps__messaging__tab--selected');
            $(this).addClass('cat__apps__messaging__tab--selected');
        });

        ///////////////////////////////////////////////////////////
        // CUSTOM SCROLL
        if (!(/Mobi/.test(navigator.userAgent)) && jQuery().jScrollPane) {
            $('.custom-scroll').each(function() {
                $(this).jScrollPane({
                    autoReinitialise: true,
                    autoReinitialiseDelay: 100
                });
                var api = $(this).data('jsp'),
                        throttleTimeout;
                $(window).bind('resize', function() {
                    if (!throttleTimeout) {
                        throttleTimeout = setTimeout(function() {
                            api.reinitialise();
                            throttleTimeout = null;
                        }, 50);
                    }
                });
            });
        }

        ///////////////////////////////////////////////////////////
        // ADJUSTABLE TEXTAREA
        autosize($('.adjustable-textarea'));

    });
</script>
<!-- END: Page Scripts -->



 

<?include('footer2.php');?>
<script src="/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function(){
    $('textarea').each(function(e){
        CKEDITOR.replace( this.id);
    });
});
</script>
</body>
</html>