@foreach($supplyItems as $item)
<tr data-item-id="{{ $item->id }}">
    <td class="item-name">{{ $item->name }}</td>
    <td class="item-description">{{ $item->description }}</td>
    <td class="item-price">{{ $item->price }}</td>
    <td class="stock-quantity">{{ $item->stock_quantity }}</td>
    <td>
        <input type="number" class="form-control order-quantity" value="1" min="1" max="{{ $item->stock_quantity }}">
    </td>
</tr>
@endforeach
