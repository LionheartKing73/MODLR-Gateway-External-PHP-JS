<?
include_once("lib/lib.php");
include_once("lib/header.php");
?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	
		<title>MODLR » Profile</title>
		
<?
include_once("lib/body_start.php");



?>
		<!--
		<div class="row">
			<div class="col-md-12">
				<ul class="breadcrumbs-alt">
					<li>
						<a href="/home/">Home</a>
					</li>
					<li>
						<a class="active-trail active" href="#">Pages</a>
					</li>
					<li>
						<a class="current" href="#">Elements</a>
					</li>
				</ul>
			</div>
		</div>
	-->

        <div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <div class="panel-body profile-information">
                       <div class="col-md-3">
                           <div class="profile-pic text-center">
                               <img src="/images/logo_square-300x300.png" alt=""/>
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="profile-desk">
                               <h1><? echo session('name');?></h1>
                               <span class="text-muted">Managing Director</span><br/>
                               <p>
                                   Please configure your personal details on the settings tab below.  
                               </p>
                               <!-- <a href="#" class="btn btn-primary">View Profile</a> -->
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="profile-statistics">
                               <h1>1240</h1>
                               <p>This Week Sales</p>
                               <h1>$5,61,240</h1>
                               <p>This Week Earn</p>
                               <ul>
                                   <li>
                                       <a href="#">
                                           <i class="fa fa-facebook"></i>
                                       </a>
                                   </li>
                                   <li class="active">
                                       <a href="#">
                                           <i class="fa fa-twitter"></i>
                                       </a>
                                   </li>
                                   <li>
                                       <a href="#">
                                           <i class="fa fa-google-plus"></i>
                                       </a>
                                   </li>
                               </ul>
                           </div>
                       </div>
                    </div>
                </section>
            </div>
            <div class="col-md-12">
                <section class="panel">
                    <header class="panel-heading tab-bg-dark-navy-blue">
                        <ul class="nav nav-tabs nav-justified ">
                            <li class="active">
                                <a data-toggle="tab" href="#overview">
                                    Overview
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#settings">
                                    Settings
                                </a>
                            </li>
                        </ul>
                    </header>
                    <div class="panel-body">
                        <div class="tab-content tasi-tab">
                            <div id="overview" class="tab-pane active">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="recent-act">
                                            <h1>Recent Activity</h1>
                                            <div class="activity-icon terques">
                                                <i class="fa fa-check"></i>
                                            </div>
                                            <div class="activity-desk">
                                                <h2>1 Hour Ago</h2>
                                                <p>Purchased new stationary items for head office</p>
                                            </div>
                                            <div class="activity-icon red">
                                                <i class="fa fa-beer"></i>
                                            </div>
                                            <div class="activity-desk">
                                                <h2 class="red">2 Hour Ago</h2>
                                                <p>Completed Coffee meeting with <a href="#" class="terques">Stive Martin</a> regarding the Product Promotion</p>
                                            </div>
                                            <div class="activity-icon purple">
                                                <i class="fa fa-tags"></i>
                                            </div>
                                            <div class="activity-desk">
                                                <h2 class="purple">today evening</h2>
                                                <p>3 photo Uploaded on facebook product page</p>
                                                <div class="photo-gl">
                                                    <a href="#">
                                                        <img src="images/sm-img-1.jpg" alt=""/>
                                                    </a>
                                                    <a href="#">
                                                        <img src="images/sm-img-2.jpg" alt=""/>
                                                    </a>
                                                    <a href="#">
                                                        <img src="images/sm-img-3.jpg" alt=""/>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="activity-icon green">
                                                <i class="fa fa-map-marker"></i>
                                            </div>
                                            <div class="activity-desk">
                                                <h2 class="green">yesterday</h2>
                                                <p>Outdoor visit at <a href="#" class="blue">California State Route</a> 85 with <a href="#" class="terques">John Boltana</a> & <a href="#" class="terques">Harry Piterson</a> regarding to setup a new show room.</p>
                                                <div class="loc-map">
                                                    location map goes here
                                                </div>
                                            </div>

                                            <div class="activity-icon yellow">
                                                <i class="fa fa-user-md"></i>
                                            </div>
                                            <div class="activity-desk">
                                                <h2 class="yellow">12 december 2013</h2>
                                                <p>Montly Regular Medical check up at Greenland Hospital.</p>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="prf-box">
                                            <h3 class="prf-border-head">work in progress</h3>
                                            <div class=" wk-progress">
                                                <div class="col-md-5">Themeforest</div>
                                                <div class="col-md-5">
                                                    <div class="progress  ">
                                                        <div style="width: 70%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="40" role="progressbar" class="progress-bar progress-bar-danger">
                                                            <span class="sr-only">70% Complete (success)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">70%</div>
                                            </div>
                                            <div class=" wk-progress">
                                                <div class="col-md-5">Graphics River</div>
                                                <div class="col-md-5">
                                                    <div class="progress ">
                                                        <div style="width: 57%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="40" role="progressbar" class="progress-bar progress-bar-success">
                                                            <span class="sr-only">57% Complete (success)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">57%</div>
                                            </div>
                                            <div class=" wk-progress">
                                                <div class="col-md-5">Code Canyon</div>
                                                <div class="col-md-5">
                                                    <div class="progress ">
                                                        <div style="width: 20%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="40" role="progressbar" class="progress-bar progress-bar-info">
                                                            <span class="sr-only">20% Complete (success)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">20%</div>
                                            </div>
                                            <div class=" wk-progress">
                                                <div class="col-md-5">Audio Jungle</div>
                                                <div class="col-md-5">
                                                    <div class="progress ">
                                                        <div style="width: 30%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="40" role="progressbar" class="progress-bar progress-bar-warning">
                                                            <span class="sr-only">30% Complete (success)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">30%</div>
                                            </div>
                                        </div>
                                        <div class="prf-box">
                                            <h3 class="prf-border-head">performance status</h3>
                                            <div class=" wk-progress pf-status">
                                                <div class="col-md-8 col-xs-8">Total Product Sales</div>
                                                <div class="col-md-4 col-xs-4">
                                                    <strong>23545</strong>
                                                </div>
                                            </div>
                                            <div class=" wk-progress pf-status">
                                                <div class="col-md-8 col-xs-8">Total Product Refer</div>
                                                <div class="col-md-4 col-xs-4">
                                                    <strong>235</strong>
                                                </div>
                                            </div>
                                            <div class=" wk-progress pf-status">
                                                <div class="col-md-8 col-xs-8">Total Earn</div>
                                                <div class="col-md-4 col-xs-4">
                                                    <strong>235452344$</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="prf-box">
                                            <h3 class="prf-border-head">team members</h3>
                                            <div class=" wk-progress tm-membr">
                                                <div class="col-md-2 col-xs-2">
                                                    <div class="tm-avatar">
                                                        <img src="images/lock_thumb.jpg" alt=""/>
                                                    </div>
                                                </div>
                                                <div class="col-md-7 col-xs-7">
                                                    <span class="tm">John Boltana</span>
                                                </div>
                                                <div class="col-md-3 col-xs-3">
                                                    <a href="#" class="btn btn-white">Assign</a>
                                                </div>
                                            </div>
                                            <div class=" wk-progress tm-membr">
                                                <div class="col-md-2 col-xs-2">
                                                    <div class="tm-avatar">
                                                        <img src="images/avatar-mini-2.jpg" alt=""/>
                                                    </div>
                                                </div>
                                                <div class="col-md-7 col-xs-7">
                                                    <span class="tm">John Boltana</span>
                                                </div>
                                                <div class="col-md-3 col-xs-3">
                                                    <a href="#" class="btn btn-white">Assign</a>
                                                </div>
                                            </div>
                                            <div class=" wk-progress tm-membr">
                                                <div class="col-md-2 col-xs-2">
                                                    <div class="tm-avatar">
                                                        <img src="images/avatar-mini-3.jpg" alt=""/>
                                                    </div>
                                                </div>
                                                <div class="col-md-7 col-xs-7">
                                                    <span class="tm">John Boltana</span>
                                                </div>
                                                <div class="col-md-3 col-xs-3">
                                                    <a href="#" class="btn btn-white">Assign</a>
                                                </div>
                                            </div>
                                            <div class=" wk-progress tm-membr">
                                                <div class="col-md-2 col-xs-2">
                                                    <div class="tm-avatar">
                                                        <img src="images/avatar-mini-4.jpg" alt=""/>
                                                    </div>
                                                </div>
                                                <div class="col-md-7 col-xs-7">
                                                    <span class="tm">John Boltana</span>
                                                </div>
                                                <div class="col-md-3 col-xs-3">
                                                    <a href="#" class="btn btn-white">Assign</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="settings" class="tab-pane ">
                                <div class="position-center">
                                    <div class="prf-contacts sttng">
                                        <h2>  Personal Information</h2>
                                    </div>
                                    <form role="form" class="form-horizontal">
                                        <!--
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label"> Avatar</label>
                                            <div class="col-lg-6">
                                                <input type="file" id="exampleInputFile" class="file-pos">
                                            </div>
                                        </div>
                                        -->
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Company</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="c-name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Lives In</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="lives-in" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Country</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="country" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Description</label>
                                            <div class="col-lg-10">
                                                <textarea rows="10" cols="30" class="form-control" id="" name=""></textarea>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="prf-contacts sttng">
                                        <h2> socail networks</h2>
                                    </div>
                                    <form role="form" class="form-horizontal">
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Facebook</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="fb-name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Twitter</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="twitter" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Google plus</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="g-plus" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Flicr</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="flicr" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Youtube</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="youtube" class="form-control">
                                            </div>
                                        </div>

                                    </form>
                                    <div class="prf-contacts sttng">
                                        <h2>Contact</h2>
                                    </div>
                                    <form role="form" class="form-horizontal">
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Address 1</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="addr1" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Address 2</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="addr2" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Phone</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="phone" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Cell</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="cell" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Email</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="email" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Skype</label>
                                            <div class="col-lg-6">
                                                <input type="text" placeholder=" " id="skype" class="form-control">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <button class="btn btn-primary" type="submit">Save</button>
                                                <button class="btn btn-default" type="button">Cancel</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>



<?
include_once("lib/body_end.php");
?>
<!--tree-->
<script src="/js/fuelux/js/tree.min.js"></script>
<!--script for this page-->
<script src="/js/tree.js"></script>
<?
include_once("lib/footer.php");
?>
