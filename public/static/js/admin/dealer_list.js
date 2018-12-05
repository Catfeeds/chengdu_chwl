new Vue({
	el: '#dealer_list',
	data: {
		list:[],
		sn:''
	},                                                            
	created: function () {
	  	var _self = this;
	  	base.ajax({
	      type:'get',
	      url:WEB_CONFIG.API_URL + 'admin/distribution',
	      data:{
	      	limit:10
	      }
	 	},function(res){
	 		//格式化数据
	      $.each(res.data,function(i,n){
	      	var _list = {
		      	sn:'',
		      	nickname:'',
		      	money:0,
		      	pay_time:'——',
		      	first_mobile:'——',
		      	first_money:'——',
		      	second_mobile:'——',
		      	second_money:'——',
		      	team_mobile:'——',
		      	team_money:'——'
		      };
	      	_list.sn = n.sn;
	      	_list.nickname = n.user.nickname;
	      	_list.money = n.money;
	      	_list.pay_time = n.pay_time;
	      	$.each(n.account_record,function(j,m){
	      		if(m.object_type==1){
	      			if(m.user_talent){
	      				_list.first_mobile = m.user_talent.mobile;
	      			}	      			
	      			_list.first_money = m.money;
	      		}
	      		else if(m.object_type==2){//团队分销
	      			if(m.user_talent){
	      				_list.team_mobile = m.user_talent.mobile;
	      			}	      			
	      			_list.team_money = m.money;
	      		}
	      		else if(m.object_type==6){
	      			if(m.user_talent){
	      				_list.second_mobile = m.user_talent.mobile;
	      			}	      			
	      			_list.second_money = m.money;
	      		}
	      	})
	      	_self.list.push(_list);
	      });

	      layui.use('laypage', function(){
            var page = layui.laypage;
            page.render({
					elem: 'pages',
					count: res.last_page,
					curr:res.current_page,
					limit:1,
					jump: function(obj,first){
					  	if(!first){
					      _self.get_list(obj.curr,false);
					  	}
					}
            });
        	});
	  	},function(res){

	  	});

	},
	mounted:function(){
		_self = this;
		//时间选择器
		layui.use('laydate', function(){
			var laydate = layui.laydate;
			laydate.render({
				elem: '#begin_time'
				,type: 'datetime'
			});
			laydate.render({
				elem: '#end_time'
				,type: 'datetime'
			});
		})       
	},
	methods:{
	  	get_list:function(num,search){
	      var _self = this;
	      base.ajax({
	         type:'get',
	         url:WEB_CONFIG.API_URL+ 'admin/distribution',
	         data:{
					sn:_self.sn,
					start_pay_time:$('#begin_time').val(),
					end_pay_time:$('#end_time').val(),
					page:num,
					limit:10
	         }
	      },function(res){
	         _self.list = [];
	         //格式化数据
	         $.each(res.data,function(i,n){
		      	var _list = {
			      	sn:'',
			      	nickname:'',
			      	money:0,
			      	pay_time:'——',
			      	first_mobile:'——',
			      	first_money:'——',
			      	second_mobile:'——',
			      	second_money:'——',
			      	team_mobile:'——',
			      	team_money:'——'
			      };
		      	_list.sn = n.sn;
		      	_list.nickname = n.user.nickname;
		      	_list.money = n.money;
		      	_list.pay_time = n.pay_time;
		      	$.each(n.account_record,function(j,m){
		      		if(m.object_type==1){
		      			if(m.user_talent){
		      				_list.first_mobile = m.user_talent.mobile;
		      			}	      			
		      			_list.first_money = m.money;
		      		}
		      		else if(m.object_type==2){//团队分销
		      			if(m.user_talent){
		      				_list.team_mobile = m.user_talent.mobile;
		      			}	      			
		      			_list.team_money = m.money;
		      		}
		      		else if(m.object_type==6){
		      			if(m.user_talent){
		      				_list.second_mobile = m.user_talent.mobile;
		      			}	      			
		      			_list.second_money = m.money;
		      		}
		      	})
		      	_self.list.push(_list);
		      });

	         if(search){
	            layui.use('laypage', function(){
                  var page = layui.laypage;
                  page.render({
							elem: 'pages',
							count: res.last_page,
							curr:res.current_page,
							limit:1,
							jump: function(obj,first){
							  	if(!first){
							      _self.get_list(obj.curr,false);
							  	}
							}
                  });
              	}); 
	         }
	      },function(res){

	      });          
	  	},
	  	to_member:function(_id){
	  		var _self = this;
	  		$.cookie('team_id', _id, base.cookieConfig(60000));
	  		window.location.href = WEB_CONFIG.WEB_URL + 'teamMember';
	  	},

	}//methods

});