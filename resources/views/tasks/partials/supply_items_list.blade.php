@foreach($supplyItems as $item)
<tr data-item-id="{{ $item->id }}">
    <td class="item-name">{{ $item->name }}</td>
    <td>{{ $item->description }}</td>
    <td class="item-price">{{ $item->price }}</td>
    <td>{{ $item->stock_quantity }}</td>
    <td>
        <input type="number" name="order[{{ $item->id }}]" class="form-control order-quantity" min="0" max="{{ $item->stock_quantity }}" value="0">
    </td>
</tr>
@endforeach
