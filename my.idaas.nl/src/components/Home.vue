<template>
 <div>
        
        <!--================Home Banner Area =================-->
        <section class="home_banner_area">
            <div class="banner_inner">
				<div class="container">
					<div class="row">
						<div class="col-lg-5">
							<div class="banner_content">
								<h2>Identity <br />playground</h2>
								<p>Play with the latest and greatest Identity as a Service technologies. Innovate quicker.</p>
								<a class="banner_btn" href="#">Get Started</a>
								<a class="banner_btn2" href="#" @click.prevent="login()">Register Now</a>
							</div>
						</div>
						<div class="col-lg-7">
							<div class="home_left_img">
								<img class="img-fluid" src="../assets/img/banner/home-left-1.png" alt="">
							</div>
						</div>
					</div>
				</div>
            </div>
        </section>
        <!--================End Home Banner Area =================-->
        
        <!--================Work Area =================-->
        <section class="work_area p_120">
        	<div class="container">
        		<div class="main_title">
        			<h2>We put you in control</h2>
        			<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</p>
        		</div>
        		<div class="work_inner row">
        			<div class="col-lg-4">
        				<div class="work_item">
        					<i class="lnr lnr-screen"></i>
        					<a href="#"><h4>Identity first</h4></a>
        					<p>Here, I focus on a range of items and features that we use in life without giving them a second thought such as Coca Cola.</p>
        				</div>
        			</div>
        			<div class="col-lg-4">
        				<div class="work_item">
        					<i class="lnr lnr-code"></i>
        					<a href="#"><h4>Stunning Visuals</h4></a>
        					<p>Here, I focus on a range of items and features that we use in life without giving them a second thought such as Coca Cola.</p>
        				</div>
        			</div>
        			<div class="col-lg-4">
        				<div class="work_item">
        					<i class="lnr lnr-clock"></i>
        					<a href="#"><h4>Maximum control</h4></a>
        					<p>As an appliance.</p>
        				</div>
        			</div>
        		</div>
        	</div>
        </section>
        <!--================End Work Area =================-->
        
        <!--================Made Life Area =================-->
        <section class="made_life_area p_120">
        	<div class="container">
        		<div class="made_life_inner">
					<ul class="nav nav-tabs" id="myTab" role="tablist">
						<li class="nav-item">
						<a class="nav-link" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Easy to use</a>
						</li>
						<li class="nav-item">
						<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Unlimited Colors</a>
						</li>
						<li class="nav-item">
						<a class="nav-link active" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Unique Features</a>
						</li>
						<li class="nav-item">
						<a class="nav-link" id="edge-tab" data-toggle="tab" href="#edge" role="tab" aria-controls="edge" aria-selected="false">Unique Features</a>
						</li>
					</ul>
					<div class="tab-content" id="myTabContent">
						<div class="tab-pane fade" id="home" role="tabpanel" aria-labelledby="home-tab">
							<div class="row made_life_text">
								<div class="col-lg-6">
									<div class="left_side_text">
										<h3>We’ve made a life <br />that will change you</h3>
										<h6>We are here to listen from you deliver exellence</h6>
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod temp or incididunt ut labore et dolore magna aliqua. Ut enim ad minim.</p>
										<a class="main_btn" href="#">Get Started Now</a>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="chart_img">
										<img class="img-fluid" src="../assets/img/chart.jpg" alt="">
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
							<div class="row made_life_text">
								<div class="col-lg-6">
									<div class="left_side_text">
										<h3>We’ve made a life <br />that will change you</h3>
										<h6>We are here to listen from you deliver exellence</h6>
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod temp or incididunt ut labore et dolore magna aliqua. Ut enim ad minim.</p>
										<a class="main_btn" href="#">Get Started Now</a>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="chart_img">
										<img class="img-fluid" src="../assets/img/chart.jpg" alt="">
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade show active" id="contact" role="tabpanel" aria-labelledby="contact-tab">
							<div class="row made_life_text">
								<div class="col-lg-6">
									<div class="left_side_text">
										<h3>We’ve made a life <br />that will change you</h3>
										<h6>We are here to listen from you deliver exellence</h6>
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod temp or incididunt ut labore et dolore magna aliqua. Ut enim ad minim.</p>
										<a class="main_btn" href="#">Get Started Now</a>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="chart_img">
										<img class="img-fluid" src="../assets/img/chart.jpg" alt="">
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="edge" role="tabpanel" aria-labelledby="edge-tab">
							<div class="row made_life_text">
								<div class="col-lg-6">
									<div class="left_side_text">
										<h3>We’ve made a life <br />that will change you</h3>
										<h6>We are here to listen from you deliver exellence</h6>
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod temp or incididunt ut labore et dolore magna aliqua. Ut enim ad minim.</p>
										<a class="main_btn" href="#">Get Started Now</a>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="chart_img">
										<img class="img-fluid" src="../assets/img/chart.jpg" alt="">
									</div>
								</div>
							</div>
						</div>
					</div>
        		</div>
        	</div>
        </section>
        <!--================End Made Life Area =================-->
        
        <!--================Screen Area =================-->
        <section class="screen_area text-center p_120">
        	<div class="container">
        		<div class="main_title">
        			<h2>Unique Screens that work perfectly</h2>
        			<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</p>
        		</div>
        		<img class="img-fluid" src="../assets/img/banner/home-left-1.png" alt="">
        	</div>
        </section>
        <!--================End Screen Area =================-->
        
        <!--================Made Life Area =================-->
        <section class="made_life_area p_120">
        	<div class="container">
        		<div class="made_life_inner">
					<div class="row made_life_text">
						<div class="col-lg-6">
							<div class="left_side_text">
								<h3>We’ve made a life <br />that will change you</h3>
								<h6>We are here to listen from you deliver exellence</h6>
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod temp or incididunt ut labore et dolore magna aliqua. Ut enim ad minim.</p>
								<a class="main_btn" href="#">Get Started Now</a>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="chart_img">
								<img class="img-fluid" src="../assets/img/browser.png" alt="">
							</div>
						</div>
					</div>
        		</div>
        	</div>
        </section>
        <!--================End Made Life Area =================-->
        
        <!--================Price Area =================-->
        <section class="price_area p_120">
        	<div class="container">
        		<div class="main_title">
        			<h2>Choose Your Price Plan</h2>
        			<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</p>
        		</div>
        		<div class="price_inner row">
        			<div class="col-lg-4">
        				<div class="price_item">
        					<div class="price_head">
        						<h4>Real Basic</h4>
        					</div>
        					<div class="price_body">
        						<ul class="list">
        							<li><a href="#">2.5 GB Space</a></li>
        							<li><a href="#">Secure Online Transfer</a></li>
        							<li><a href="#">Unlimited Styles</a></li>
        							<li><a href="#">Customer Service</a></li>
        						</ul>
        					</div>
        					<div class="price_footer">
        						<h3><span class="dlr">$</span> 39 <span class="month">Per <br />Month</span></h3>
        						<a class="main_btn" href="#">Get Started</a>
        					</div>
        				</div>
        			</div>
        			<div class="col-lg-4">
        				<div class="price_item">
        					<div class="price_head">
        						<h4>Real Standard</h4>
        					</div>
        					<div class="price_body">
        						<ul class="list">
        							<li><a href="#">10 GB Space</a></li>
        							<li><a href="#">Secure Online Transfer</a></li>
        							<li><a href="#">Unlimited Styles</a></li>
        							<li><a href="#">Customer Service</a></li>
        						</ul>
        					</div>
        					<div class="price_footer">
        						<h3><span class="dlr">$</span> 69 <span class="month">Per <br />Month</span></h3>
        						<a class="main_btn" href="#">Get Started</a>
        					</div>
        				</div>
        			</div>
        			<div class="col-lg-4">
        				<div class="price_item">
        					<div class="price_head">
        						<h4>Real Ultimate</h4>
        					</div>
        					<div class="price_body">
        						<ul class="list">
        							<li><a href="#">Unlimited Space</a></li>
        							<li><a href="#">Secure Online Transfer</a></li>
        							<li><a href="#">Unlimited Styles</a></li>
        							<li><a href="#">Customer Service</a></li>
        						</ul>
        					</div>
        					<div class="price_footer">
        						<h3><span class="dlr">$</span> 99 <span class="month">Per <br />Month</span></h3>
        						<a class="main_btn" href="#">Get Started</a>
        					</div>
        				</div>
        			</div>
        		</div>
        	</div>
        </section>
        <!--================End Price Area =================-->

        <!--================Made Life Area =================-->
        <section class="made_life_area made_white p_120">
        	<div class="container">
        		<div class="made_life_inner">
					<div class="row made_life_text">
						<div class="col-lg-6">
							<div class="chart_img">
								<img class="img-fluid" src="../assets/img/banner/home-left-1.png" alt="">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="left_side_text">
								<h3>We’ve made a life <br />that will change you</h3>
								<h6>We are here to listen from you deliver exellence</h6>
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod temp or incididunt ut labore et dolore magna aliqua. Ut enim ad minim.</p>
								<a class="main_btn" href="#">Get Started Now</a>
							</div>
						</div>
					</div>
        		</div>
        	</div>
        </section>
        <!--================End Made Life Area =================-->
        
        <!--================Impress Area =================-->
        <section class="impress_area p_120">
        	<div class="container">
        		<div class="impress_inner">
					<h2>Got Impressed by our features?</h2>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore  et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</p>
					<a class="banner_btn2" href="#">Request Free Demo</a>
        		</div>
        	</div>
        </section>
        <!--================End Impress Area =================-->
      
 </div>
</template>

<script>

import {toLogin, toLogout} from '../util'

export default {
  name: 'Home',
  
  mounted(){
	  console.log('hello world!');
  },

  methods: {
	  login(){
		  toLogin();
	  }
  }


 
}

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

</style>
