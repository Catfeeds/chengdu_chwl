@extends('../layouts.main')

@section('css')
<link href="{{statics('iconfont/iconfont.css')}}" rel="stylesheet">
@endsection

@section('content')

<div id="talentApp">

    <div class="t1">姓名：</div>
    <div class="t2">
        <input type="text" v-model="name" type="text" placeholder="请输入您的姓名" autocomplete="off">
    </div>

    <div class="t1">手机号码：</div>
    <div class="t2">
        <input type="text" v-model="mobile" type="text" placeholder="请输入您的手机号码" autocomplete="off">
    </div>

    <div class="t3"><button @click="talent()">提交申请</button></div>

    <div class="t4">
        <p>客服热线：400-888-888</p>
        <p>微信：123123123</p>
        <p>工作时间：09:30-21:30</p>
    </div>

    <div class="t5">
        <a href="tel:400-888-888"><i class="iconfont icon-bodadianhua" style="font-size: 1rem;color: #44a92f;"></i></a>
    </div>


</div>


@endsection

@section('javascript')

<script src="{{statics('js/ints/talent.js')}}"></script>

@endsection