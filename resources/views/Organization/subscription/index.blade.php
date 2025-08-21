@extends('layouts.app')

@section('title', 'My Subscription')

@section('content_header')
    <h1>My Subscription</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo e(session('success')); ?>

    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo e(session('error')); ?>

    </div>
@endif

<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="subscription-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="current-plan-tab" data-toggle="pill" href="#current-plan" role="tab">Current Plan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="history-tab" data-toggle="pill" href="#history" role="tab">History</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="subscription-tabs-content">
            {{-- Current Plan Tab --}}
            <div class="tab-pane fade show active" id="current-plan" role="tabpanel">
                <?php if($currentSubscription && $plan): ?>
                    {{-- --- THIS IS THE DEFINITIVE FIX FOR THE UI --- --}}
                    <h3 class="text-info"><?php echo e($plan->name); ?></h3>
                    <p class="lead"><b>£<?php echo e(number_format($plan->price, 2)); ?></b> / <?php echo e($plan->type); ?></p>
                    <p class="text-muted"><?php echo e($plan->description); ?></p>
                    <hr>
                    <p><strong>Started On:</strong> <?php echo e($currentSubscription->created_at->format('d M Y')); ?></p>
                    <p>
                        <strong>Status:</strong>
                        <?php if($currentSubscription->canceled()): ?>
                            <span class="badge badge-warning">Canceled</span>
                            Your subscription is set to end and will not renew.
                        <?php else: ?>
                            <span class="badge badge-success">Active</span>
                             Your subscription will automatically renew.
                        <?php endif; ?>
                    </p>
                    <p>
                        <strong>
                            <?php if($currentSubscription->canceled()): ?>
                                Ends on:
                            <?php else: ?>
                                Renews on:
                            <?php endif; ?>
                        </strong>
                        <?php echo e($currentSubscription->calculated_ends_at->format('d M Y')); ?>

                    </p>

                    <a href="<?php echo e(route('pricing')); ?>" class="btn btn-primary mt-3">Change Plan</a>
                <?php else: ?>
                    <p class="text-muted">You are not currently subscribed to any plan.</p>
                    <a href="<?php echo e(route('pricing')); ?>" class="btn btn-primary">View Plans</a>
                <?php endif; ?>
            </div>

            {{-- History Tab --}}
            <div class="tab-pane fade" id="history" role="tabpanel">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Plan Name</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $allSubscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(optional($subscription->plan)->name ?? 'Unknown Plan'); ?></td>
                                <td>
                                    <?php if($subscription->canceled()): ?>
                                        <span class="badge badge-danger">Ended</span>
                                    <?php elseif($subscription->active()): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?php echo e(ucfirst($subscription->stripe_status)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($subscription->created_at->format('d M Y')); ?></td>
                                <td><?php echo e(optional($subscription->ends_at)->format('d M Y') ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No subscription history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop