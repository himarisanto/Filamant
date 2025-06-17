<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Filament\Resources\KelasResource\RelationManagers;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_kelas')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tingkat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('gurus')
                    ->relationship('gurus', 'nama')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Guru Pengajar')
                    ->required(),
                Forms\Components\Textarea::make('deskripsi')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kelas')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tingkat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('siswas_count')
                    ->label('Jumlah Siswa')
                    ->counts('siswas')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Kelas $record) {
                        if ($record->siswas()->count() > 0) {
                            Notification::make()
                                ->title('Tidak dapat menghapus kelas')
                                ->body('Kelas ini masih memiliki siswa yang terdaftar.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }

                        if ($record->gurus()->count() > 0) {
                            Notification::make()
                                ->title('Tidak dapat menghapus kelas')
                                ->body('Kelas ini masih memiliki guru yang mengajar.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, Collection $records) {
                            $shouldCancel = false;

                            foreach ($records as $record) {
                                if ($record->siswas()->count() > 0) {
                                    Notification::make()
                                        ->title('Tidak dapat menghapus kelas')
                                        ->body("Kelas {$record->nama_kelas} masih memiliki siswa yang terdaftar.")
                                        ->danger()
                                        ->send();

                                    $shouldCancel = true;
                                }

                                if ($record->gurus()->count() > 0) {
                                    Notification::make()
                                        ->title('Tidak dapat menghapus kelas')
                                        ->body("Kelas {$record->nama_kelas} masih memiliki guru yang mengajar.")
                                        ->danger()
                                        ->send();

                                    $shouldCancel = true;
                                }
                            }

                            if ($shouldCancel) {
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GurusRelationManager::class,
            RelationManagers\SiswasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'view' => Pages\ViewKelas::route('/{record}'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}
