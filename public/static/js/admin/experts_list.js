new Vue({
	el: '#experts_list',
	data: {
		list:[],
		status:'all',
		nickname:'',
		name:'',
		mobile:''
	},                                                            
	created: function () {
	  	var _self = this;
	  	base.ajax({
	      type:'get',
	      url:WEB_CONFIG.API_URL + 'admin/user_talent',
	      data:{
	      	status:'all',
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
	methods:{
	  	get_list:function(num,search){
	      var _self = this;
	      base.ajax({
	         type:'get',
	         url:WEB_CONFIG.API_URL+ 'admin/user_talent',
	         data:{
					nickname:_self.nickname,
					name:_self.name,
					status:_self.status,
					mobile:_self.mobile,
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
	  		layer.confirm('确定要冻结该用户？', function(index){
	  			base.ajax({
			      type:'put',
			      url:WEB_CONFIG.API_URL + 'admin/user_talent/'+_id,
			      data:{
			      	status:2
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
	  		layer.confirm('确定要解冻该用户？', function(index){
	  			base.ajax({
			      type:'put',
			      url:WEB_CONFIG.API_URL + 'admin/user_talent/'+_id,
			      data:{
			      	status:1
			      }
			 	},function(res){
			      _self.list[_index].status = 1;
			      layer.close(index);
			  	},function(res){
			  		layer.close(index);
			  	});
	  		})
	  	},
	  	show_code:function(){
	  		var _self = this;
	  		var $data = {
            random: base.uuid(16, Math.floor(Math.random() * (75 - 16 + 1) + 16)),
            timestamp: Date.parse(new Date()) / 1000
        	};

        	var encrypt = new JSEncrypt();
        	encrypt.setPublicKey();
        	var encryptData = encrypt.encrypt(JSON.stringify($data));

		  	$.ajax({
            type:'get',
            url: WEB_CONFIG.API_URL + 'admin/user_talent_qrcode',
            data:{},
            dataType: 'json',
            headers: {
            	'X-Requested-With': 'XMLHttpRequest',
                sign: encryptData,
                random: $data.random,
                timestamp: $data.timestamp,
               token: $.cookie('chwlToken')
            },
            complete:function (data) {
               layer.open({
               	title:'达人注册二维码',
			      	content:'<div style="text-align:center">'+
			      	'<img src="'+data.responseText+'"/><br><br>'+
			      	'<a href="'+data.responseText+'" download="达人注册二维码.png" id="xx">下载二维码<a></div>'
			    //   	btn:['下载二维码','取消'],
			    //   	yes:function(index){
			    //   		var imgUrl = data.responseText;
			    //   		if (window.navigator.msSaveOrOpenBlob) {
			      			
							// 	var bstr = window.atob(imgUrl.split(',')[1])
							// 	alert(1);
							// 	var n = bstr.length;
							// 	var u8arr = new Uint8Array(n);
							// 	while (n--) {
							// 		u8arr[n] = bstr.charCodeAt(n)
							// 	};
							// 	var blob = new Blob([u8arr]);
							// 	window.navigator.msSaveOrOpenBlob(blob, 'chart-download' + '.' + 'png');
							// } else {
							// 	// 这里就按照chrome等新版浏览器来处理
							// 	// layer.close(index)
							// 	const a = document.createElement('a');
							// 	a.href = data.responseText;
							// 	a.setAttribute('download','达人注册二维码.png');
							// 	a.click();
							// }
			    //   	}

			      })
            }
        	})

	  	}

	}//methods

});