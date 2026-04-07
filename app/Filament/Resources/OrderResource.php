<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderDetailsRelationManager;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('date')

                    ->default(now())
                    ->disabled()
                    ->dehydrated()
                    ->prefix('Order Date')
                    ->columnSpanFull(),
                Group::make()
                    ->schema([


                        Section::make('Customer Information')
                            ->columns(3)
                            ->description('Fill in the details of the order.')
                            ->schema([

                                Forms\Components\Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $customer = \App\Models\Customer::find($state);

                                        $set('phone', $customer->phone ?? null);
                                        $set('address', $customer->address ?? null);
                                    }),
                                Placeholder::make('phone')
                                    ->content(fn(Get $get) => $get('customer_id') ? Customer::find($get('customer_id'))->phone : 'Select a customer to view phone number'),

                                Placeholder::make('address')
                                    ->content(fn(Get $get) => $get('customer_id') ? Customer::find($get('customer_id'))->address : 'Select a customer to view address')

                            ]),

                        Section::make('Details')
                            ->description('Order Details')
                            ->schema([
                                Repeater::make('orderDetails')
                                    ->relationship()

                                    ->schema([
                                        Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->reactive()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $product = Product::find($state);
                                                $price = $product ? $product->price : 0;
                                                $set('price', $price);
                                                $quantity = $get('quantity') ?? 0;
                                                $set('quantity', $quantity);
                                                $subtotal = $price * $quantity;
                                                $set('subtotal', $subtotal);

                                                $items = $get('../../orderDetails') ?? [];
                                                $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                                $set('../../total_price', $total);

                                                $discount = $get('../../discount') ?? 0;
                                                $discount_amount = $total * ($discount / 100);
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            }),
                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->reactive()
                                            ->default(1)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $price = $get('price') ?? 0;
                                                $subtotal = $price * $state;
                                                $set('subtotal', $subtotal);
                                                $items = $get('../../orderDetails') ?? [];
                                                $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                                $set('../../total_price', $total);
                                                $discount = $get('../../discount') ?? 0;
                                                $discount_amount = $total * ($discount / 100);
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            }),
                                        TextInput::make('price')
                                            ->disabled()
                                            ->numeric()
                                            ->dehydrated()
                                            ->formatStateUsing(fn($state, Get $get)
                                            => $state ?? Product::find($get('product_id'))->price ?? 0)
                                            ->prefix('Rp. '),
                                        TextInput::make('subtotal')
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->prefix('Rp. ')
                                            ->numeric()

                                    ])->columns(4)
                            ]),
                        Forms\Components\TextInput::make('total_price')
                            ->disabled()
                            ->required()
                            ->numeric()
                            ->dehydrated()
                            ->prefix('Rp. '),
                    ])->columnSpan(2),
                Section::make('Additional Information')
                    ->description('Payment Information')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'cancelled' => 'Cancelled',
                                'completed' => 'Completed',
                            ])
                            ->default('new')
                            ->columnSpanFull(1)
                            ->required(),
                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->columnSpanFull()
                            ->dehydrated(),
                        TextInput::make('discount')
                            ->columnSpan(2)
                            ->reactive()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('%')
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $discount = floatval($state ?? 0);
                                $total_price = $get('total_price') ?? 0;
                                $discount_amount = $total_price * ($discount / 100);
                                $set('discount_amount', $discount_amount);
                                $set('total_payment', $total_price - $discount_amount);
                            }),
                        TextInput::make('discount_amount')
                            ->columnSpan(2)
                            ->disabled()
                            ->dehydrated()
                            ->prefix('Rp. '),
                        TextInput::make('total_payment')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->prefix('Rp. '),
                    ])->columnSpan(1)
                    ->columns(4),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->prefix('Rp. ')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')->suffix('%')->sortable(),
                TextColumn::make('discount_amount')->prefix('Rp. ')->sortable(),
                TextColumn::make('total_payment')->prefix('Rp. ')->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'primary',
                        'processing' => 'warning',
                        'cancelled' => 'danger',
                        'completed' => 'success',
                    })->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderDetailsRelationManager::class,
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
