@extends('layouts.appmain')

@section('main_navbar')
    @include('layouts.navbar')
@endsection

{{--<script>--}}
{{--function getFormById(id, id_input, value) {--}}
{{--    document.getElementById(id_input).value = value;--}}
{{--    event.preventDefault();--}}
{{--    document.getElementById(id).submit()--}}
{{--}--}}
{{--</script>--}}

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <form id="search-form" method="POST" action="{{ route('search') }}">
                    @csrf
                    <div class="form-row col-12">
                        <div class="col-10">
                            <input id="input-search" class="form-control" value="{{ $old??'' }}" type="text" name="search" placeholder="البحث عن الموظف إما برقم التوظيف أو الاسم." required>
                        </div>
                        <div class="col-2">
                            <input class="btn btn-primary btn-block" value="ابحث" type="submit">
                        </div>
                    </div>
                </form>

                @if($errors->has('search'))
                <div class="col-12 text-right text-danger font-weight-bolder small">
                    <span>
                        {{ $errors->first('search') }}
                    </span>
                </div>
                @endif
{{--                @if($errors->has('production'))--}}
{{--                    <div class="col-12 text-right font-weight-bolder small pl-4 mt-4">--}}
{{--                        <div class="alert alert-danger">--}}
{{--                            {{ $errors->first('production') }}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endif--}}

                @if(isset($message))
                    <div class="col-12 text-right mt-3 pl-4">
                        <div class="alert alert-{{ ($state)?'success':'info' }} small">
                            <img src="{{ asset('images/icons/' . ($state? 'update-icon.svg':'add.svg')) }}" class="app-filter-{{ ($state? 'green':'blue') }}" width="16px" >
                            <span class="mr-1">
                            {{ $message }}
                            </span>
                        </div>
                    </div>
                @endif

                @if(isset($data))
                    @if($data->count() === 1)
                    <form method="POST" action="{{ route('store') }}">
                        <div class="col-12 pl-4 mt-4">
                            <div class="card">
                                <div class="card-header app-card-table">
                                    <table class="table table-borderless text-center m-0">
                                        <thead>
                                            <tr>
                                                <td width="10%">رقم الموظف</td>
                                                <td width="30%">اسم الموظف</td>
                                                <td width="40%">الإنتاج الحالي</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="card-body app-card-table">
                                    <table class="table table-borderless text-center m-0">
                                        <thead>
                                        <tr>
                                            <td width="10%">{{ $data->first()['id_emp'] }}</td>
                                            <td width="30%">{{ $data->first()['name'] }}</td>
                                            <td width="40%" class="text-right">
                                                <input type="text" class="form-control" name="id_emp" value="{{ $data->first()['id_emp'] }}" hidden>
                                                <input type="text" class="form-control" name="name" value="{{ $data->first()['name'] }}" hidden>
                                                <input type="text" class="form-control" name="production">
                                                @if($errors->has('production'))
                                                <small class="small text-danger">{{ $errors->first('production') }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12">
                            @csrf
                            <input type="submit" class="btn btn-primary btn-block mt-4" value="تحديث">
                        </div>
                    </form>
                    @else
                        <div class="col-12 pt-3 pl-4 text-right">
                            <div class="list-group">
                                @foreach($data as $item)
                                    <a  class="list-group-item list-group-item-action"
                                        onclick="getForm({{ $item['id_emp'] }});">
                                        <span class="badge-info badge-pill small"><strong>{{ $item['id_emp'] }}</strong></span>
                                        <span class="mr-4">{{ $item['name'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="col-12 text-center mt-5">
                        <span class="text-muted">{{ $messageStart }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('main_footer')
    @include('layouts.footer')
@endsection