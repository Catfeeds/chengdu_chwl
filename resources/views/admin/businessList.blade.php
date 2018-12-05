@extends('../admin.layout.main')

@section('content')
<style>

.anchorBL{ 
display:none;
}

</style>
<div class="layui-body layui-tab-content site-demo-body">
	<div class="info_box" id="business_list" v-cloak>
		<div class="search_bar">
			<div class="form-inline">
				<div class="form-group pl30">
			    	<label>注册时间：</label>
			    	<input type="text" class="form-control" placeholder="请选择开始时间" id="begin_time"> -
			    	<input type="text" class="form-control" placeholder="请选择结束时间" id="end_time">
			  	</div>
			  	<div class="form-group">
			    	<label>商家名称：</label>
			    	<input type="text" class="form-control" v-model="name">
			  	</div>
			  	<div class="form-group pl30">
					<button class="layui-btn" @click="get_list(1,true)">搜索</button>
					<button class="layui-btn layui-btn-normal" @click="add()">新增商家+</button>
			  	</div>				  	
			</div>
		</div>
		<div>
			<table class="layui-table">
				<colgroup>
					<col width="12%">
					<col width="12%">	
					<col width="16%">
					<col width="15%">
					<col width="15%">
					<col width="10%">	
					<col width="20%">
				</colgroup>
				<thead>
					<tr>
						<th>商家名称</th>
						<th>商家联系电话</th>
						<th>商家地址</th>
						<th>核销系统用户名</th>
						<th>核销登录手机号</th>
						<th>状态</th>
						<th>操作</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(n,index) in list">
						<td>@{{n.name}}</td>
						<td>@{{n.tel}}</td>
						<td>@{{n.address}}</td>
						<td>@{{n.username}}</td>
						<td>@{{n.mobile}}</td>
						<td>@{{n.status==1?'正常':'冻结'}}</td>
						<td>
							<button class="layui-btn layui-btn-sm" @click="edit(index)">修改商家信息</button>
							<button class="layui-btn layui-btn-sm layui-btn-danger" v-if="n.status==1" @click="freeze(n.id,index)">冻结</button>
							<button class="layui-btn layui-btn-sm layui-btn-danger" v-if="n.status==2" @click="unfreeze(n.id,index)">解冻</button>
						</td>
					</tr>
				</tbody>
			</table>
			<div id="pages" class="tc mg20"></div>
			<!--弹出层-->
			<div class="cen" v-show="add_show" v-cloak>
				<p class="tc mg20">新增商家信息</p>
				<table class="layui-table" style="width: 90%; margin:0 5%;">
					<colgroup>
					   <col width="40%">
					   <col width="60%">			   
				   </colgroup>
				   <tr>
						<td class="b">商家名称</td>
						<td><input type="text" class="layui-input" v-model.trim="req.name"></td>
				   </tr>
				   <tr>
						<td class="b">联系电话</td>
						<td>
							<input type="text" class="layui-input" v-model.trim="req.tel">
						</td>
				  	</tr>
				  	<tr>
						<td class="b">商家地址</td>
						<td><input type="text" class="layui-input" v-model="req.address" readonly></td>
				  	</tr>
				  	<tr>
						<td class="b" colspan="2">
							<div id="map_1" style="width: 100%;height: 300px;" @click="add_point()"></div>
						</td>					
				  	</tr>
				  	<tr>
						<td class="b">核销系统用户名</td>
						<td><input type="text" class="layui-input" v-model="req.username"></td>
				  	</tr>
				  	<tr>
						<td class="b">登录手机号码</td>
						<td><input type="text" class="layui-input" v-model="req.mobile"></td>
				  	</tr>
				  	<tr>
						<td class="b">登录密码</td>
						<td><input type="password" class="layui-input" v-model="req.password"></td>
				  	</tr>
				  	<tr>
						<td class="b">确认登录密码</td>
						<td><input type="password" class="layui-input" v-model="req.re_password"></td>
				  	</tr>
				</table>
				<p class="mg20 tc">
					<button class="layui-btn layui-btn-normal" style="margin:0 20px;" @click="add_post()">保存</button>
					<button class="layui-btn layui-btn-primary" @click="close()">取消</button>	
				</p>								
			</div>

			<div class="cen" v-show="edit_show" v-cloak>
				<p class="tc mg20">修改物业数据</p>
				<table class="layui-table" style="width: 90%; margin:0 5%;">
					<colgroup>
					   <col width="40%">
					   <col width="60%">			   
				   </colgroup>
				    <tr>
						<td class="b">商家名称</td>
						<td><input type="text" class="layui-input" v-model.trim="req.name" disabled></td>
				   </tr>
				   <tr>
						<td class="b">联系电话</td>
						<td>
							<input type="text" class="layui-input" v-model.trim="req.tel">
						</td>
				  	</tr>
				  	<tr>
						<td class="b">商家地址</td>
						<td><input type="text" class="layui-input" v-model="req.address" readonly></td>
				  	</tr>
				  	<tr>
						<td class="b" colspan="2">
							<div id="map_2" style="width: 100%;height: 300px;" @click="edit_point()"></div>
						</td>					
				  	</tr>
				  	<tr>
						<td class="b">核销系统用户名</td>
						<td><input type="text" class="layui-input" v-model="req.username"></td>
				  	</tr>
				  	<tr>
						<td class="b">登录手机号码</td>
						<td><input type="text" class="layui-input" v-model="req.mobile"></td>
				  	</tr>
				</table>
				<p class="mg20 tc">
					<button class="layui-btn layui-btn-normal" style="margin:0 20px;" @click="edit_post()">保存</button>
					<button class="layui-btn layui-btn-primary" @click="close()">取消</button>	
				</p>								
			</div>
			<div class="cover" v-show="cover" v-cloak></div>
			<!--弹出层end-->
		</div>

	</div>
</div>
	
@endsection

@section('javascript')
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=Rl9uCg4u9ngVPGlYvRf47g1FxtSej8Vn"></script>
<script src="{{statics('js/admin/business_list.js')}}"></script>
<script src="{{statics('js/md5.js')}}"></script>
<script src="{{statics('js/sha256.js')}}"></script>
<script src="{{statics('js/jsencrypt.min.js')}}"></script>
<script type="text/javascript">
var map_1 = new BMap.Map("map_1");
map_1.enableScrollWheelZoom();
map_1.centerAndZoom(new BMap.Point(104.072282, 30.663154), 18);

var map_2 = new BMap.Map("map_2");
map_2.enableScrollWheelZoom();
map_2.centerAndZoom(new BMap.Point(104.072282, 30.663154), 18);
</script>
@endsection