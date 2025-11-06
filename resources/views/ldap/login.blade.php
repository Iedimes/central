@extends('brackets/admin-ui::admin.layout.master')

@section('title', 'Login LDAP')

@section('content')
    <div class="container" id="app">
        <div class="row align-items-center justify-content-center auth">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-block">
                        <auth-form :action="'{{ route('ldap.login.submit') }}'" :data="{}" inline-template>
                            <form class="form-horizontal" role="form" method="POST" action="{{ route('ldap.login.submit') }}" novalidate>
                                @csrf
                                <div class="auth-header">
                                    <h1 class="auth-title">Acceso LDAP</h1>
                                    <p class="auth-subtitle">Ingrese sus credenciales corporativas</p>
                                </div>

                                <div class="auth-body">
                                    @if(session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            {{ $errors->first() }}
                                        </div>
                                    @endif

                                    <div class="form-group" :class="{'has-danger': errors.has('username'), 'has-success': fields.username && fields.username.valid }">
                                        <label for="username">Usuario</label>
                                        <div class="input-group input-group--custom">
                                            <div class="input-group-addon"><i class="input-icon input-icon--user"></i></div>
                                            <input type="text" v-model="form.username" v-validate="'required'" class="form-control"
                                                   :class="{'form-control-danger': errors.has('username'), 'form-control-success': fields.username && fields.username.valid}"
                                                   id="username" name="username" placeholder="Ingrese su usuario LDAP" value="{{ old('username') }}">
                                        </div>
                                        <div v-if="errors.has('username') && fields.username && fields.username.touched"
                                            class="form-control-feedback form-text" v-cloak>
                                            @{{ errors.first('username') }}
                                        </div>

                                    </div>

                                    <div class="form-group" :class="{'has-danger': errors.has('password'), 'has-success': fields.password && fields.password.valid }">
                                        <label for="password">Contraseña</label>
                                        <div class="input-group input-group--custom">
                                            <div class="input-group-addon"><i class="input-icon input-icon--lock"></i></div>
                                            <input type="password" v-model="form.password" v-validate="'required'" class="form-control"
                                                   :class="{'form-control-danger': errors.has('password'), 'form-control-success': fields.password && fields.password.valid}"
                                                   id="password" name="password" placeholder="Ingrese su contraseña">
                                        </div>
                                        <div v-if="errors.has('password') && fields.password && fields.password.touched"
                                            class="form-control-feedback form-text" v-cloak>
                                            @{{ errors.first('password') }}
                                        </div>

                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block btn-spinner">
                                            <i class="fa"></i> Ingresar
                                        </button>
                                    </div>

                                    {{-- <div class="form-group text-center">
                                        <a href="{{ url('/admin/password-reset') }}" class="auth-ghost-link">¿Olvidó su contraseña?</a>
                                    </div> --}}
                                </div>
                            </form>
                        </auth-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('bottom-scripts')
<script type="text/javascript">
    document.getElementById('password').dispatchEvent(new Event('input'));
</script>
@endsection
