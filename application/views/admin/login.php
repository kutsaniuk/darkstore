<?include('header.php');?>
<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Horizontal Form</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form id="form-user_login" class="login-form" action="javascript:void(null);" method="post" OnSubmit="ajax_post('login',this,'#res-user_login');">
                        
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Email</label>

                  <div class="col-sm-10">
					<input required="" type="text" class="form-control" id="user_name" name="email" autocomplete="off" placeholder="E-mail">
                           
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Password</label>

                  <div class="col-sm-10">
                   <input required="" type="password" class="form-control" id="user_pass" name="password" autocomplete="off" placeholder="Password">
                          
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                      <label>
                         <input type="checkbox" class="checkbox" value="1" name="remember" id="remember_me"> Remember me
                      </label>
                    </div>
                  </div>
                </div>
              </div>
			  <div id="res-user_login"></div>
              <!-- /.box-body -->
              <div class="box-footer">
                
                <button type="submit" class="btn btn-info pull-right">Sign in</button>
              </div>
              <!-- /.box-footer -->
            </form>
          </div> 
<?include('footer.php');?>      