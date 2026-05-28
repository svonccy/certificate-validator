<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del usuario')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->prefixIcon('heroicon-m-user')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Radio::make('role')
                                    ->label('Rol de Acceso')
                                    ->options(UserRole::class)
                                    ->descriptions([
                                        UserRole::Admin->value => 'Acceso completo a seguridad, gestión de usuarios, certificados y borrado de registros.',
                                        UserRole::Operador->value => 'Acceso enfocado a la creación, firma y verificación de certificados y titulares.',
                                    ])
                                    ->default(UserRole::Operador)
                                    ->inline()
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('password')
                                    ->label('Contraseña')
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->password()
                                    ->dehydrated(fn(?string $state): bool => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->helperText(fn(string $context): string => $context === 'create'
                                        ? 'Ingrese una contraseña segura para el nuevo usuario.'
                                        : 'Deje este campo en blanco si no desea modificar la contraseña actual.'
                                    )
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
