<div class="flex flex-col p-10">
    <livewire:doctor-card :doctor="$doctor" :hideButton="true" wire:locale-changed="refresh" :key="$doctor->id" >
    <div class="flex flex-col mt-5">

        <form wire:submit="submit">
            @if($this->byStaff == true)
            <div class="mb-5">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{__("Contact Number")}}</label>
                <input type="text"
                        id="name"
                        name="name"
                        wire:model="whatsApp"
                        class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    required />
            </div>
            @else
            <div class="mb-5">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"> {{__("Whats App")}}</label>
                <input type="text" id="name"
                    class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    disabled value="{{$this->appointment->booking_whatsapp_number}}" />
            </div>
            @endif
            <div class="mb-5">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{__("Your Name")}}</label>
                <input type="text"
                        id="name"
                        name="name"
                        wire:model="name"
                        class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    required />
            </div>
            <div class="mb-5">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{__("Your Email")}}</label>
                <input type="email"
                    name="email"
                    id="email"
                    wire:model="email"
                    class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    />
            </div>
            @if($this->byStaff != true)
            <div class="flex items-start mb-5">
                <div class="flex items-center h-5">
                    <input id="terms" type="checkbox" wire:model.live="showContactInput"
                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800"
                         />
                </div>
                <label for="terms" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{__("I dont use my whatsapp number to recieve phone calls.")}}</label>
            </div>
            @endif
            @if($this->showContactInput == true)
            <div class="mb-5">
                <label for="contact" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{__("Contact Number")}}</label>
                <input type="text"
                name="contact"
                wire:model="contact"
                id="contact"
                    class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    required value="" />
            </div>
            @endif
            <div class=" mb-5">

                <h3 class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{__("Select Date")}}
                </h3>
                <ul class="flex flex-wrap w-full gap-2 ">
                    @foreach ($this->doctor->availableDates($this->mode) as $date)
                    <li>
                        <input
                        type="radio" id="date-{{$date}}" name="selected-date" value="{{$date}}" class="hidden peer"
                            required />
                        <label for="date-{{$date}}"
                            wire:click="selectDate('{{$date}}')"
                            class="inline-flex items-center justify-between px-4 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
                            <div class="block">
                                <div class="w-full text-sm font-semibold">{{$date}}</div>
                            </div>
                        </label>
                    </li>
                    @endforeach

                </ul>

            </div>
            <div class=" mb-5">

                <h3 class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{__("Select Time")}}
                </h3>
                <ul class="flex flex-wrap w-full gap-2 ">
                    @foreach ($this->availableTimeSlots as $slot)
                    <li>
                        <input type="radio" id="slot-{{$slot}}" name="selected-time" value="today" class="hidden peer"
                            required />
                        <label for="slot-{{$slot}}"
                        wire:click="selectTime('{{$slot}}')"
                            class="inline-flex items-center justify-between px-4 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
                            <div class="block">
                                <div class="w-full text-sm font-semibold">{{date("g:i A", strtotime($slot))}}</div>
                            </div>

                        </label>
                    </li>
                    @endforeach


                </ul>

            </div>
            <button type="submit"
                class="text-gray-900 w-full bg-gradient-to-r from-teal-200 to-lime-200 hover:bg-gradient-to-l hover:from-teal-200 hover:to-lime-200 focus:ring-4 focus:outline-none focus:ring-lime-200 dark:focus:ring-teal-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">{{__("Book Now")}}</button>

        </form>

    </div>
</div>
