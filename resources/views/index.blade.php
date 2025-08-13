<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script>@extends('layouts.main')

@section('title', 'لیست کارها')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center">لیست کارها</h1>
    <a href="{{ route('todos.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-4 inline-block">ایجاد کار جدید</a>
    <div class="space-y-4">
        @forelse ($todos as $todo)
            <div class="bg-white p-4 rounded-lg shadow-md flex justify-between items-center {{ $todo->is_completed ? 'opacity-50' : '' }}">
                <div>
                    <h3 class="text-xl font-semibold">{{ $todo->title }}</h3>
                    <p class="text-gray-600">{{ $todo->description }}</p>
                    <p class="text-sm text-gray-500">ایجاد شده در: {{ $todo->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div class="space-x-2">
                    @if (!$todo->is_completed)
                        <a href="{{ route('todos.complete', $todo->id) }}" class="text-green-600 hover:underline">تکمیل</a>
                        <a href="{{ route('todos.edit', $todo->id) }}" class="text-blue-600 hover:underline">ویرایش</a>
                    @endif
                    <a href="{{ route('todos.history', $todo->id) }}" class="text-purple-600 hover:underline">تاریخچه</a>
                    <form action="{{ route('todos.destroy', $todo->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('آیا مطمئن هستید؟')">حذف</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500">هیچ کاری یافت نشد.</p>
        @endforelse
    </div>
</div>
@endsection