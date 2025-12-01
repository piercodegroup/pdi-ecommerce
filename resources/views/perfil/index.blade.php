@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-color-secondary mb-8">Meu Perfil</h1>

    <div class="bg-white rounded-lg border p-6">
        <form action="{{ route('perfil.atualizar') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome', $cliente->nome) }}"
                        class="w-full border rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}"
                        class="w-full border rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Telefone</label>
                    <input type="text" name="telefone" value="{{ old('telefone', $cliente->telefone) }}"
                        class="w-full border rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">CPF</label>
                    <input type="text" name="cpf" 
                        value="{{ old('cpf', $cliente->cpf) }}"
                        class="w-full border rounded-lg px-4 py-2 {{ $cliente->cpf && !is_null($cliente->senha) ? 'bg-gray-100' : '' }}"
                        {{ ($cliente->cpf && !is_null($cliente->senha)) ? 'readonly' : '' }}
                        placeholder="Informe seu CPF">
                    
                    @if(is_null($cliente->senha) && !$cliente->cpf)
                        <p class="text-sm text-blue-600 mt-1">Complete seu cadastro informando o CPF</p>
                    @elseif($cliente->cpf && !is_null($cliente->senha))
                        <p class="text-sm text-gray-500 mt-1">CPF já cadastrado</p>
                    @endif
                    
                    <p class="text-xs text-gray-400 mt-1">
                        Debug: Senha: {{ is_null($cliente->senha) ? 'Nula (social)' : 'Definida' }}, 
                        CPF: {{ $cliente->cpf ? 'Definido' : 'Nulo' }}
                    </p>
                </div>
            </div>

            <h2 class="text-xl font-bold text-color-secondary mt-8 mb-4">Alterar Senha</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(!is_null($cliente->senha))
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Senha Atual</label>
                    <input type="password" name="senha_atual" class="w-full border rounded-lg px-4 py-2">
                </div>
                @else
                <div>
                    <p class="text-sm text-blue-600 bg-blue-50 p-3 rounded-lg">
                        <strong>Conta criada via Google</strong><br>
                        Defina uma senha para acessar com email e senha.
                    </p>
                </div>
                @endif

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Nova Senha</label>
                    <input type="password" name="nova_senha" class="w-full border rounded-lg px-4 py-2"
                        placeholder="{{ is_null($cliente->senha) ? 'Defina sua senha' : 'Deixe em branco para manter a atual' }}">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Confirmar Nova Senha</label>
                    <input type="password" name="nova_senha_confirmation" class="w-full border rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="bg-color-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CPF - corrigida para permitir 14 caracteres
    const cpfInput = document.querySelector('input[name="cpf"]');
    if (cpfInput && !cpfInput.readOnly) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            // Aplicar máscara apenas se tiver dígitos
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.replace(/(\d{3})(\d+)/, '$1.$2');
                } else if (value.length <= 9) {
                    value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1.$2.$3');
                } else {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d+)/, '$1.$2.$3-$4');
                }
            }
            
            e.target.value = value;
        });

        // Também adicionar evento para quando o campo perder o foco
        cpfInput.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length === 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                e.target.value = value;
            }
        });
    }

    const telefoneInput = document.querySelector('input[name="telefone"]');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d+)/, '($1) $2');
            }
            
            e.target.value = value;
        });
    }

    console.log('Campo CPF:', cpfInput);
    console.log('CPF readonly:', cpfInput ? cpfInput.readOnly : 'não encontrado');
    console.log('CPF value:', cpfInput ? cpfInput.value : 'não encontrado');
});
</script>
@endsection