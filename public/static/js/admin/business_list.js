new Vue({
	el: '#business_list',
	data: {
		list:[],
		name:'',
		add_show:false,
      edit_show:false,
      cover:false,
      req:{
         id:null,
         name:0,
         tel:0,
         address:'haha',
         lng:0,
         lat:0,
         username:0,
         mobile:0,
         password:null,
         salt:'sabwdkww',
         re_password:null
     	}
	},                                                            
	created: function () {
	  	var _self = this;
	  	base.ajax({
	      type:'get',
	      url:WEB_CONFIG.API_URL + 'admin/business',
	      data:{
	      	limit:10
	      }
	 	},function(res){
	      _self.list = res.data;
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
	         url:WEB_CONFIG.API_URL+ 'admin/business',
	         data:{
					name:_self.name,
					start_time:$('#begin_time').val(),
					end_time:$('#end_time').val(),
					page:num,
					limit:10
	         }
	      },function(res){
	         _self.list = res.data;
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
	  	freeze:function(_id,_index){
	  		var _self = this;
	  		layer.confirm('确定要冻结该商家？', function(index){
	  			base.ajax({
			      type:'put',
			      url:WEB_CONFIG.API_URL + 'admin/business/'+_id,
			      data:{
			      	status:2,
			      	update_type:'status'
			      }
			 	},function(res){
			      _self.list[_index].status = 2;
			      layer.close(index);
			  	},function(res){
			  		layer.close(index);
			  	});
	  		})
	  	},
	  	unfreeze:function(_id,_index){
	  		var _self = this;
	  		layer.confirm('确定要解冻该商家？', function(index){
	  			base.ajax({
			      type:'put',
			      url:WEB_CONFIG.API_URL + 'admin/business/'+_id,
			      data:{
			      	status:1,
			      	update_type:'status'
			      }
			 	},function(res){
			      _self.list[_index].status = 1;
			      layer.close(index);
			  	},function(res){
			  		layer.close(index);
			  	});
	  		})
	  	},
	  	close:function(){
         var _self = this;
         _self.add_show = false;
         _self.edit_show = false;
         _self.cover = false;
      },
      add:function(){
         var _self = this;
         _self.req = {
            id:null,
            name:'',
	         tel:'',
	         address:'',
	         lng:0,
	         lat:0,
	         username:'',
	         mobile:'',
	         password:null,
	         re_password:null
         }
         _self.add_show = true;
         _self.cover = true;
      },
      edit:function(_index){
         var _self = this;
         //深拷贝
         _self.req = JSON.parse(JSON.stringify(_self.list[_index]));
         _self.edit_show = true;
         _self.cover = true;
         map_2.centerAndZoom(new BMap.Point(_self.req.lng, _self.req.lat), 20);
      },
      add_post:function(){
         var _self = this;
         var re = /^1[3456789]\d{9}$/;
         if(_self.req.name==""){
             base.layer.msg('商家名称不能为空');
             return false;
         }
         if(_self.req.address==""){
             base.layer.msg('请选择商家地址');
             return false;
         }
         if(_self.req.tel==""){
             base.layer.msg('商家联系电话不能为空');
             return false;
         }
         if(!re.test(_self.req.mobile)){
             base.layer.msg('登录手机号码有误');
             return false;
         }
         if(_self.req.password.length<6||_self.req.password.length>18){
             base.layer.msg('密码的长度在6-18位');
             return false;
         }
         if(_self.req.password!=_self.req.re_password){
             base.layer.msg('两次输入的密码不相同');
             return false;
         }
         _self.req.salt = base.uuid(8);
         var sha256Pwd = _self.encryption(_self.req.password + _self.req.salt);

         base.ajax({
             type:'post',
             url:WEB_CONFIG.API_URL + 'admin/business',
             data:{
                 name:_self.req.name,
                 tel:_self.req.tel,
                 address:_self.req.address,
                 lng:_self.req.lng,
                 lat:_self.req.lat,
                 username:_self.req.username,
                 mobile:_self.req.mobile,
                 password:sha256Pwd,
                 salt:_self.req.salt
             }
         },function(data){
             location.reload();
         },function(data){

         }); 
      },
      edit_post:function(){
         var _self = this;
         var re = /^1[3456789]\d{9}$/;
         if(_self.req.tel==""){
             base.layer.msg('商家联系电话不能为空');
             return false;
         }
         if(_self.req.address==""){
             base.layer.msg('请选择商家地址');
             return false;
         }
         if(_self.req.username==""){
             base.layer.msg('核销系统用户名不能为空');
             return false;
         }
         if(!re.test(_self.req.mobile)){
             base.layer.msg('登录手机号码有误');
             return false;
         }
         base.ajax({
            type:'put',
            url:WEB_CONFIG.API_URL + 'admin/business/'+_self.req.id,
            data:{
            	id:_self.req.id,
					name:_self.req.name,
					tel:_self.req.tel,
					address:_self.req.address,
					lng:_self.req.lng,
					lat:_self.req.lat,
					username:_self.req.username,
					mobile:_self.req.mobile
            }
         },function(data){
            location.reload();
         },function(data){

         }); 
      },
      add_point:function(){
      	var _self = this;
      	var geoc = new BMap.Geocoder();
	      map_1.addEventListener("click", function(e){    
	        	var pot = e.point;
	        	geoc.getLocation(pot, function(rs){
	            _self.req.address = rs.address;
	            _self.req.lng = rs.point.lng;
	            _self.req.lat = rs.point.lat;
	        	});        
		   });
      },
      edit_point:function(){
      	var _self = this;
      	var geoc = new BMap.Geocoder();
	      map_2.addEventListener("click", function(e){    
	        	var pot = e.point;
	        	geoc.getLocation(pot, function(rs){
	            _self.req.address = rs.address;
	            _self.req.lng = rs.point.lng;
	            _self.req.lat = rs.point.lat;
	        	});        
		   });
      },
      //加密
      encryption:function(val){
			return hex_md5(CryptoJS.SHA256(val).toString().toUpperCase());
		}

	}//methods

});