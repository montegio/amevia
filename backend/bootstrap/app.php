<?php
use App\Domain\Family\Exceptions\FamilyMemberAlreadyExistsException;
use App\Domain\Family\Exceptions\FamilyInvitationNotPendingException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
	commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(
        fn (Request $request) => $request->is('api/*'),
    );

    $exceptions->render(function (
        FamilyMemberAlreadyExistsException $exception,
        Request $request
    ) {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 'FAMILY_MEMBER_ALREADY_EXISTS',
            ], 409);
        }
    });

    $exceptions->render(function (
	FamilyInvitationNotPendingException $exception,
        Request $request
    ) {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 'FAMILY_INVITATION_NOT_PENDING',
            ], 409);
        }
    });
})->create();
