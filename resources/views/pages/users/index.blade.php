@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Users</li>
        </ol>
    </nav>

    <div class="row">
        <div class="card-body">
            <h6 class="card-title">Manage users</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>LAST NAME</th>
                        <th>USERNAME</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>1</th>
                        <td>Mark</td>
                        <td>Otto</td>
                        <td>@mdo</td>
                    </tr>
                    <tr>
                        <th>2</th>
                        <td>Jacob</td>
                        <td>Thornton</td>
                        <td>@fat</td>
                    </tr>
                    <tr>
                        <th>3</th>
                        <td>Larry</td>
                        <td>the Bird</td>
                        <td>@twitter</td>
                    </tr>
                    <tr>
                        <th>4</th>
                        <td>Larry</td>
                        <td>Jellybean</td>
                        <td>@lajelly</td>
                    </tr>
                    <tr>
                        <th>5</th>
                        <td>Larry</td>
                        <td>Kikat</td>
                        <td>@lakitkat</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <ul class="pagination justify-content-center">
        <li class="paginate_button page-item previous disabled">
            <a href="#"
               aria-controls="dataTableExample"
               data-dt-idx="0"
               tabindex="0"
               class="page-link">Previous</a>
        </li>

        <li class="paginate_button page-item active">
            <a href="#" aria-controls="dataTableExample" data-dt-idx="1"
               tabindex="0" class="page-link">1</a>
        </li>

        <li class="paginate_button page-item ">
            <a href="#" aria-controls="dataTableExample" data-dt-idx="2" tabindex="0"
               class="page-link">2</a>
        </li>

        <li class="paginate_button page-item ">
            <a href="#" aria-controls="dataTableExample" data-dt-idx="3" tabindex="0"
               class="page-link">3</a>
        </li>

        <li class="paginate_button page-item next" id="dataTableExample_next">
            <a href="#"
               aria-controls="dataTableExample"
               data-dt-idx="4" tabindex="0"
               class="page-link">Next</a>
        </li>
    </ul>
@endsection
