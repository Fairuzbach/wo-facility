<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approval Work Order ({{ $role }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-bold mb-4">Daftar Tiket Menunggu Persetujuan</h3>

                    @if ($approvals->count() > 0)
                        <table class="min-w-full border-collapse block md:table">
                            <thead class="block md:table-header-group">
                                <tr
                                    class="border border-grey-500 md:border-none block md:table-row absolute -top-full md:top-auto -left-full md:left-auto  md:relative ">
                                    <th
                                        class="bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell">
                                        Tiket #</th>
                                    <th
                                        class="bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell">
                                        Pelapor</th>
                                    <th
                                        class="bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell">
                                        Deskripsi</th>
                                    <th
                                        class="bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="block md:table-row-group">
                                @foreach ($approvals as $item)
                                    <tr class="bg-gray-300 border border-grey-500 md:border-none block md:table-row">
                                        <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell">
                                            {{ $item->ticket_num }}</td>
                                        <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell">
                                            {{ $item->requester_name }} <br>
                                            <span class="text-xs text-gray-500">{{ $item->requester_division }}</span>
                                        </td>
                                        <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell">
                                            {{ $item->description }}</td>
                                        <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell">
                                            <form action="{{ route('fh.approve', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 border border-blue-700 rounded">
                                                    Approve
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500 text-center py-4">Tidak ada tiket yang perlu disetujui saat ini.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
