@extends('admin.layouts.master')

@section('title', 'Comment')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Comment</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Table</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Comment list</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive table-data ">

                        @if (session("success"))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session("success")}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle text-center"
                               style="width:100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Product</th>
                                <th>Content</th>
                                <th>Rating</th>
                                <th>Active</th>
                                <th>Create at</th>
                                <th>Update at</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody id="product-list">
                                @foreach($comments as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>
                                            {{ \Illuminate\Support\Str::limit($item->user->name, 15, '...') }}
                                        </td>
                                        <td>
                                            {{ \Illuminate\Support\Str::limit($item->product->name, 15, '...') }}
                                        </td>
                                        <td>
                                            {{ \Illuminate\Support\Str::limit($item->content, 15, '...') }}
                                        </td>
                                        <td>
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $item->rate)
                                                    <i class="fa fa-star text-warning"></i> 
                                                @else
                                                    <i class="fa fa-star text-light"></i>
                                                @endif
                                            @endfor
                                        </td>
                                        
                                        <td>{!! $item->is_active ? '<span class="badge bg-primary">Active</span>' : '<span class="badge bg-danger">No active</span>' !!}</td>
                                        <td>
                                            <span id="invoice-date">{{ $item->created_at ? $item->created_at->format('d M, Y') : 'N/A' }}</span>
                                            <small class="text-muted" id="invoice-time">{{ $item->created_at ? $item->created_at->format('h:iA') : '' }}</small>
                                        </td>
                                        <td>
                                            <span id="invoice-date">{{ $item->updated_at ? $item->updated_at->format('d M, Y') : 'N/A' }}</span>
                                            <small class="text-muted" id="invoice-time">{{ $item->updated_at ? $item->updated_at->format('h:iA') : '' }}</small>
                                        </td>                                        
                                        <td >
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('admin.comments.edit', $item) }}" class="btn btn-primary btn-sm">Edit 
                                                    <i class="fa-regular fa-pen-to-square fa-sm"></i>
                                                </a>
                                                <form action="{{ route('admin.comments.destroy', $item) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="return confirm('Are you sure you want to delete?')" type="submit" class="btn btn-danger btn-sm">Delete 
                                                        <i class="fa-solid fa-delete-left fa-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between">
                            <div>
                                <p>Showing {{ $comments->firstItem() }} to {{ $comments->lastItem() }} of {{ $comments->total() }} comments</p>
                            </div>
                            <div>
                                {{ $comments->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style-libs')

@endsection



