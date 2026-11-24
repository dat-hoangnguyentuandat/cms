@extends('layouts.admin')
@section('title', isset($localBusiness) ? 'Sửa LocalBusiness' : 'Thêm LocalBusiness')

@section('content')
<div class="card">
  <div class="card-header">
    <h3>{{ isset($localBusiness) ? 'Sửa' : 'Thêm' }} LocalBusiness</h3>
    <a href="{{ route('admin.local-businesses.index') }}" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="post"
        action="{{ isset($localBusiness) ? route('admin.local-businesses.update', $localBusiness) : route('admin.local-businesses.store') }}">
    @csrf
    @if(isset($localBusiness)) @method('PUT') @endif

    <h4 style="margin:0 0 16px">Thông tin cơ bản</h4>
    <div class="form-row">
      <div class="form-group">
        <label>Tên doanh nghiệp <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $localBusiness->name ?? '') }}" required>
      </div>
      <div class="form-group">
        <label>Schema Type</label>
        <select name="schema_type" class="form-control">
          @foreach([
            'LocalBusiness' => 'LocalBusiness (mặc định)',
            'Restaurant' => 'Restaurant (Nhà hàng)',
            'CafeOrCoffeeShop' => 'Cafe / Coffee Shop',
            'Store' => 'Store (Cửa hàng)',
            'Hotel' => 'Hotel (Khách sạn)',
            'Hospital' => 'Hospital (Bệnh viện)',
            'MedicalClinic' => 'Medical Clinic',
            'Dentist' => 'Dentist (Nha khoa)',
            'Attorney' => 'Attorney (Luật sư)',
            'BankOrCreditUnion' => 'Bank / Credit Union',
            'BeautySalon' => 'Beauty Salon',
            'ExerciseGym' => 'Exercise Gym',
            'AutomotiveBusiness' => 'Automotive Business',
          ] as $value => $label)
            <option value="{{ $value }}" {{ old('schema_type', $localBusiness->schema_type ?? 'LocalBusiness') == $value ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả doanh nghiệp</label>
      <textarea name="description" class="form-control" rows="3">{{ old('description', $localBusiness->description ?? '') }}</textarea>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label>Website URL</label>
        <input type="url" name="url" class="form-control" value="{{ old('url', $localBusiness->url ?? '') }}" placeholder="https://example.com">
      </div>
      <div class="form-group">
        <label>Logo URL</label>
        <input type="url" name="logo_url" class="form-control" value="{{ old('logo_url', $localBusiness->logo_url ?? '') }}" placeholder="https://...">
      </div>
      <div class="form-group">
        <label>Image URL</label>
        <input type="url" name="image_url" class="form-control" value="{{ old('image_url', $localBusiness->image_url ?? '') }}" placeholder="https://...">
      </div>
    </div>

    <h4 style="margin:24px 0 16px">Liên hệ</h4>
    <div class="form-row-3">
      <div class="form-group">
        <label>Số điện thoại</label>
        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $localBusiness->telephone ?? '') }}" placeholder="+84123456789">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $localBusiness->email ?? '') }}" placeholder="info@example.com">
      </div>
      <div class="form-group">
        <label>Google Map URL</label>
        <input type="url" name="google_map_url" class="form-control" value="{{ old('google_map_url', $localBusiness->google_map_url ?? '') }}" placeholder="https://maps.google.com/...">
      </div>
    </div>

    <h4 style="margin:24px 0 16px">Địa chỉ</h4>
    @php
      $address = old('address', $localBusiness->address ?? []);
    @endphp
    <div class="form-group">
      <label>Địa chỉ (Street)</label>
      <input type="text" name="address[street]" class="form-control" value="{{ $address['street'] ?? '' }}" placeholder="123 Phố Huế">
    </div>
    <div class="form-row-3">
      <div class="form-group">
        <label>Thành phố (City)</label>
        <input type="text" name="address[city]" class="form-control" value="{{ $address['city'] ?? '' }}" placeholder="Hà Nội">
      </div>
      <div class="form-group">
        <label>Tỉnh/Thành (State)</label>
        <input type="text" name="address[state]" class="form-control" value="{{ $address['state'] ?? '' }}" placeholder="Hà Nội">
      </div>
      <div class="form-group">
        <label>Mã bưu điện</label>
        <input type="text" name="address[postal_code]" class="form-control" value="{{ $address['postal_code'] ?? '' }}" placeholder="100000">
      </div>
    </div>
    <div class="form-group">
      <label>Quốc gia (Country)</label>
      <input type="text" name="address[country]" class="form-control" value="{{ $address['country'] ?? '' }}" placeholder="Vietnam">
    </div>

    <h4 style="margin:24px 0 16px">Tọa độ địa lý</h4>
    @php
      $geoCoordinates = old('geo_coordinates', $localBusiness->geo_coordinates ?? []);
    @endphp
    <div class="form-row">
      <div class="form-group">
        <label>Latitude (Vĩ độ)</label>
        <input type="number" step="any" name="geo_coordinates[latitude]" class="form-control" value="{{ $geoCoordinates['latitude'] ?? '' }}" placeholder="21.0285">
      </div>
      <div class="form-group">
        <label>Longitude (Kinh độ)</label>
        <input type="number" step="any" name="geo_coordinates[longitude]" class="form-control" value="{{ $geoCoordinates['longitude'] ?? '' }}" placeholder="105.8542">
      </div>
    </div>

    <h4 style="margin:24px 0 16px">Thông tin kinh doanh</h4>
    <div class="form-row-3">
      <div class="form-group">
        <label>Price Range</label>
        <select name="price_range" class="form-control">
          <option value="">-- Chọn --</option>
          @foreach(['$', '$$', '$$$', '$$$$'] as $range)
            <option value="{{ $range }}" {{ old('price_range', $localBusiness->price_range ?? '') == $range ? 'selected' : '' }}>
              {{ $range }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Google Place ID</label>
        <input type="text" name="google_place_id" class="form-control" value="{{ old('google_place_id', $localBusiness->google_place_id ?? '') }}" placeholder="ChIJ..." readonly style="background:#f1f5f9">
        <small style="color:var(--text-2);font-size:12px">Auto-filled từ import</small>
      </div>
      <div class="form-group" style="display:flex;align-items:end;gap:12px">
        <label class="form-check">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', $localBusiness->is_active ?? 1) ? 'checked' : '' }}>
          Active
        </label>
        <label class="form-check">
          <input type="checkbox" name="is_primary" value="1" {{ old('is_primary', $localBusiness->is_primary ?? 0) ? 'checked' : '' }}>
          Primary
        </label>
      </div>
    </div>

    <h4 style="margin:24px 0 16px">Payment & Currencies</h4>
    <div class="form-group">
      <label>Payment Accepted <small style="color:var(--text-2)">(cách nhau bởi dấu phẩy)</small></label>
      @php
        $paymentAccepted = old('payment_accepted_string', isset($localBusiness) && $localBusiness->payment_accepted ? implode(', ', $localBusiness->payment_accepted) : '');
      @endphp
      <input type="text" name="payment_accepted_string" class="form-control" value="{{ $paymentAccepted }}" placeholder="cash, credit card, momo, banking">
      <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Ví dụ: cash, credit card, momo, banking</small>
    </div>
    <div class="form-group">
      <label>Currencies Accepted <small style="color:var(--text-2)">(cách nhau bởi dấu phẩy)</small></label>
      @php
        $currenciesAccepted = old('currencies_accepted_string', isset($localBusiness) && $localBusiness->currencies_accepted ? implode(', ', $localBusiness->currencies_accepted) : '');
      @endphp
      <input type="text" name="currencies_accepted_string" class="form-control" value="{{ $currenciesAccepted }}" placeholder="VND, USD">
      <small style="color:var(--text-2);font-size:12px;display:block;margin-top:4px">Ví dụ: VND, USD, EUR</small>
    </div>

    <h4 style="margin:24px 0 16px">Social Profiles</h4>
    <div class="form-group">
      <label>Facebook URL</label>
      <input type="url" name="social_profiles[facebook]" class="form-control" value="{{ old('social_profiles.facebook', $localBusiness->social_profiles['facebook'] ?? '') }}" placeholder="https://facebook.com/...">
    </div>
    <div class="form-group">
      <label>Instagram URL</label>
      <input type="url" name="social_profiles[instagram]" class="form-control" value="{{ old('social_profiles.instagram', $localBusiness->social_profiles['instagram'] ?? '') }}" placeholder="https://instagram.com/...">
    </div>
    <div class="form-group">
      <label>Twitter URL</label>
      <input type="url" name="social_profiles[twitter]" class="form-control" value="{{ old('social_profiles.twitter', $localBusiness->social_profiles['twitter'] ?? '') }}" placeholder="https://twitter.com/...">
    </div>

    @if(isset($localBusiness))
    <h4 style="margin:24px 0 16px">Rating & Reviews</h4>
    <div class="form-row">
      <div class="form-group">
        <label>Aggregate Rating</label>
        <input type="text" class="form-control" value="{{ $localBusiness->aggregate_rating ? number_format($localBusiness->aggregate_rating, 2) : '—' }}" readonly style="background:#f1f5f9">
      </div>
      <div class="form-group">
        <label>Review Count</label>
        <input type="text" class="form-control" value="{{ $localBusiness->review_count }}" readonly style="background:#f1f5f9">
      </div>
    </div>
    @if($localBusiness->google_place_id)
    <p style="color:var(--text-2);font-size:14px;margin-top:8px">
      💡 Bạn có thể <strong>đồng bộ rating</strong> từ testimonials bằng button "Sync Rating" ở trang danh sách.
    </p>
    @endif
    @endif

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">
        {{ isset($localBusiness) ? 'Cập nhật' : 'Tạo mới' }}
      </button>
      <a href="{{ route('admin.local-businesses.index') }}" class="btn btn-outline">Hủy</a>
    </div>
  </form>
</div>

@push('js')
<script>
// Convert comma-separated string to array before submit
document.querySelector('form').addEventListener('submit', function(e) {
  // Payment accepted
  const paymentInput = document.querySelector('[name="payment_accepted_string"]');
  if (paymentInput && paymentInput.value.trim()) {
    const payments = paymentInput.value.split(',').map(p => p.trim()).filter(Boolean);
    payments.forEach((payment, index) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `payment_accepted[${index}]`;
      input.value = payment;
      this.appendChild(input);
    });
  }

  // Currencies accepted
  const currencyInput = document.querySelector('[name="currencies_accepted_string"]');
  if (currencyInput && currencyInput.value.trim()) {
    const currencies = currencyInput.value.split(',').map(c => c.trim()).filter(Boolean);
    currencies.forEach((currency, index) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `currencies_accepted[${index}]`;
      input.value = currency;
      this.appendChild(input);
    });
  }
});
</script>
@endpush
@endsection
