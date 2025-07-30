<div class="flex flex-col px-3">
    @foreach ($doctors as $doctor)
    <livewire:doctor-card :doctor="$doctor" :hideOnlineAppointment="$hideOnlineAppointment" wire:locale-changed="refresh" :key="$doctor->id" >
    @endforeach
</div>
@push("bottom_html")
 <!-- drawer component -->
<div id="department-drawer" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-5/6 dark:bg-gray-800" tabindex="-1" aria-labelledby="drawer-label">
<ul role="list" class="divide-y divide-gray-100">
    <div class="mb-10">
        <image class="w-52" src="/storage/{{$client->logo_path}}" />
    </div>
    @foreach ($this->specializations as $specialization)
  <li class="flex justify-between gap-x-6 py-5">
    <div class="flex min-w-0 gap-x-4">
      <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="{{env('APP_URL').'storage/'.$specialization->icon_image}}" alt="">
      <div class="min-w-0 items-center flex flex-row">
        <p class=" font-bold text-2xl  leading-6 text-gray-900">{{$specialization->name}}</p>
      </div>
    </div>
  </li>
  @endforeach

</ul>
</div>
@endpush
