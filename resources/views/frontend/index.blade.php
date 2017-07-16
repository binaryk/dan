@extends('frontend.layouts.app')

@section('content')
    <div class="row">

        <div class="col-xs-12">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-home"></i> {{ trans('navs.general.home') }}
                </div>

                <div class="panel-body">

                    Acesta este proiectul de licenta, in care vom demonstra cum functioneaza algoritmul <b>Hamming</b> pentru recunoastere de imagini.

                    <hr>
                    <p>
                        Pasii pentru a realiza inregistrarea unui utilizator sunt:
                    </p>
                    <ol>
                        <li>Completarea datelor de utilizator (Prenume/Nume/Email/Parola si Confirmare)</li>
                        <li>Capturarea unei poze cu ajutorul camerei web</li>
                        <li>Transmiterea datelor spre procesare la server</li>
                        <li>Convertirea imaginii din format <i>base64</i> in jpeg</li>
                        <li>Stocarea imaginii pe disk (HDD)</li>
                        <li>Convertirea imaginii in biti, cu ajutorul algoritmului <b>dHash</b></li>
                        <li>Verificarea validitatii imaginii in lista de useri, cu ajutorul algoritmului <b>Hamming</b>, procesat pe biti</li>
                        <li>Daca nici un alt user nu are o poza similara mai mult de 70% cu poza incarcata de utilizatorul curent, se lasa pasul urmator: </li>
                        <li>Stocarea datelor de utilizator precum si calea spre imagine, dar si formatul ei binar, ca o inregistrare noua in tabela de <i>users</i></li>
                        <li>Daca nu se trece de validare, procesarea se opreste aici.</li>
                    </ol>

                </div>
            </div><!-- panel -->

        </div>
    </div>
@endsection

@section('afters-styles')
    <style>

    </style>
@endsection