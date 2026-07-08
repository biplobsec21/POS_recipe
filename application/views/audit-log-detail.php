<!DOCTYPE html>
<html>
<head>
   <?php include "comman/code_css_form.php"; ?>
   <style>
      .diff-changed  { background-color: #fff3cd; }
      .diff-old      { color: #dc3545; text-decoration: line-through; word-break: break-all; }
      .diff-new      { color: #28a745; word-break: break-all; }
      .diff-unchanged{ color: #6c757d; }
      .show-unchanged-btn { margin-bottom: 10px; }
      .unchanged-row { display: none; }
      .meta-card     { background: #f8f9fa; border-left: 4px solid #3c8dbc; padding: 12px 16px; margin-bottom: 16px; border-radius: 3px; }
      .history-badge { font-size: 11px; }
   </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

   <?php include "sidebar.php"; ?>

   <div class="content-wrapper">
      <section class="content-header">
         <h1><?= $page_title; ?> <small>Side-by-side diff</small></h1>
         <ol class="breadcrumb">
            <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo $base_url; ?>auditlog">Audit Log</a></li>
            <li class="active">Detail #<?= $log->id; ?></li>
         </ol>
      </section>

      <section class="content">
         <div class="row">

            <!-- ===== META CARD ===== -->
            <div class="col-md-12">
               <div class="meta-card">
                  <div class="row">
                     <div class="col-md-3">
                        <strong>Module:</strong>
                        <span class="label label-default"><?= ucfirst(str_replace('_',' ', $log->module)); ?></span>
                     </div>
                     <div class="col-md-3">
                        <strong>Action:</strong>
                        <?php
                        $badge = ['create'=>'label-success','update'=>'label-warning','delete'=>'label-danger'];
                        $bc = isset($badge[$log->action]) ? $badge[$log->action] : 'label-default';
                        ?>
                        <span class="label <?= $bc; ?>"><?= ucfirst($log->action); ?></span>
                     </div>
                     <div class="col-md-3">
                        <strong>Record:</strong>
                        <code><?= htmlspecialchars($log->record_code ?? $log->record_id); ?></code>
                        <small class="text-muted">(ID: <?= $log->record_id; ?>)</small>
                     </div>
                     <div class="col-md-3">
                        <strong>Date:</strong> <?= date('d-m-Y H:i:s', strtotime($log->created_at)); ?>
                     </div>
                  </div>
                  <div class="row" style="margin-top:8px;">
                     <div class="col-md-3">
                        <strong>User:</strong> <?= ucfirst(htmlspecialchars($log->username ?? '-')); ?>
                     </div>
                     <div class="col-md-3">
                        <strong>IP Address:</strong> <?= htmlspecialchars($log->ip_address ?? '-'); ?>
                     </div>
                     <div class="col-md-6">
                        <strong>User Agent:</strong>
                        <small class="text-muted"><?= htmlspecialchars(substr($log->user_agent ?? '', 0, 120)); ?></small>
                     </div>
                  </div>
               </div>
            </div>

            <!-- ===== DIFF TABLE ===== -->
            <div class="col-md-8">
               <div class="box box-info">
                  <div class="box-header with-border">
                     <h3 class="box-title">
                        <i class="fa fa-exchange"></i> Field Changes
                        <?php
                        $changed_count = count(array_filter($diff, fn($d) => $d['changed']));
                        ?>
                        <small><?= $changed_count; ?> field<?= $changed_count !== 1 ? 's' : ''; ?> changed</small>
                     </h3>
                     <div class="box-tools">
                        <button class="btn btn-xs btn-default show-unchanged-btn" id="toggle_unchanged">
                           <i class="fa fa-eye"></i> Show Unchanged Fields
                        </button>
                     </div>
                  </div>
                  <div class="box-body table-responsive">
                     <?php if (empty($diff)): ?>
                        <p class="text-muted text-center">No field data available for this log entry.</p>
                     <?php else: ?>
                        <table class="table table-bordered table-sm" style="font-size:13px;">
                           <thead class="bg-primary">
                              <tr>
                                 <th style="width:22%">Field</th>
                                 <th style="width:39%">Old Value</th>
                                 <th style="width:39%">New Value</th>
                              </tr>
                           </thead>
                           <tbody>
                           <?php foreach ($diff as $d): ?>

                              <?php if ($d['type'] === 'items'): ?>
                              <!-- ── ITEMS SECTION ── -->
                              <tr class="<?= $d['changed'] ? 'diff-changed' : 'unchanged-row'; ?>">
                                 <td colspan="3" style="padding:0;">
                                    <table class="table table-bordered table-condensed" style="margin:0;font-size:12px;">
                                       <thead>
                                          <tr class="bg-gray">
                                             <th style="width:10%">Item ID</th>
                                             <th style="width:45%">Before</th>
                                             <th style="width:45%">After</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                       <?php foreach ($d['item_rows'] as $ir): ?>
                                          <?php
                                          $row_class = '';
                                          if ($ir['status'] === 'added')    $row_class = 'success';
                                          if ($ir['status'] === 'removed')  $row_class = 'danger';
                                          if ($ir['status'] === 'changed')  $row_class = 'warning';
                                          ?>
                                          <tr class="<?= $row_class; ?>">
                                             <td><code><?= $ir['item_id']; ?></code></td>
                                             <td>
                                                <?php if ($ir['old']): ?>
                                                   <span class="<?= $ir['status'] !== 'unchanged' ? 'diff-old' : ''; ?>">
                                                      <?= htmlspecialchars($ir['old']); ?>
                                                   </span>
                                                <?php else: ?>
                                                   <em class="text-muted">— not present —</em>
                                                <?php endif; ?>
                                             </td>
                                             <td>
                                                <?php if ($ir['new']): ?>
                                                   <span class="<?= $ir['status'] !== 'unchanged' ? 'diff-new' : ''; ?>">
                                                      <?= htmlspecialchars($ir['new']); ?>
                                                   </span>
                                                <?php else: ?>
                                                   <em class="text-muted">— removed —</em>
                                                <?php endif; ?>
                                             </td>
                                          </tr>
                                       <?php endforeach; ?>
                                       </tbody>
                                    </table>
                                 </td>
                              </tr>

                              <?php else: ?>
                              <!-- ── REGULAR FIELD ROW ── -->
                              <tr class="<?= $d['changed'] ? 'diff-changed' : 'unchanged-row'; ?>">
                                 <td><code><?= htmlspecialchars($d['field']); ?></code></td>
                                 <td>
                                    <?php if ($d['changed']): ?>
                                       <span class="diff-old"><?= htmlspecialchars((string)($d['old'] ?? '')); ?></span>
                                    <?php else: ?>
                                       <span class="diff-unchanged"><?= htmlspecialchars((string)($d['old'] ?? '')); ?></span>
                                    <?php endif; ?>
                                 </td>
                                 <td>
                                    <?php if ($d['changed']): ?>
                                       <span class="diff-new"><?= htmlspecialchars((string)($d['new'] ?? '')); ?></span>
                                    <?php else: ?>
                                       <span class="diff-unchanged"><?= htmlspecialchars((string)($d['new'] ?? '')); ?></span>
                                    <?php endif; ?>
                                 </td>
                              </tr>
                              <?php endif; ?>

                           <?php endforeach; ?>
                           </tbody>
                        </table>
                     <?php endif; ?>
                  </div>
               </div>
            </div>

            <!-- ===== RECORD HISTORY SIDEBAR ===== -->
            <div class="col-md-4">
               <div class="box box-warning">
                  <div class="box-header with-border">
                     <h3 class="box-title"><i class="fa fa-history"></i> History for this Record</h3>
                  </div>
                  <div class="box-body" style="max-height:500px;overflow-y:auto;">
                     <?php if (empty($history)): ?>
                        <p class="text-muted">No other history entries.</p>
                     <?php else: ?>
                        <ul class="timeline timeline-inverse" style="padding-left:10px;">
                        <?php foreach ($history as $h):
                           $hb  = ['create'=>'bg-green','update'=>'bg-yellow','delete'=>'bg-red'];
                           $hbc = isset($hb[$h->action]) ? $hb[$h->action] : 'bg-gray';
                           $is_current = ((int)$h->id === (int)$log->id);
                        ?>
                           <li class="<?= $is_current ? 'time-label' : ''; ?>">
                              <?php if ($is_current): ?>
                                 <span class="<?= $hbc; ?>">Current — <?= date('d-m-Y H:i', strtotime($h->created_at)); ?></span>
                              <?php else: ?>
                                 <i class="fa fa-clock-o <?= $hbc; ?>"></i>
                                 <div class="timeline-item" style="margin-left:35px;">
                                    <span class="time"><i class="fa fa-clock-o"></i> <?= date('d-m-Y H:i', strtotime($h->created_at)); ?></span>
                                    <h3 class="timeline-header" style="font-size:12px;">
                                       <span class="label <?= str_replace('bg-','label-',$hbc); ?> history-badge"><?= ucfirst($h->action); ?></span>
                                       <?= ucfirst($h->username ?? '-'); ?>
                                    </h3>
                                    <?php if (!empty($h->changed_fields)):
                                       $cf = json_decode($h->changed_fields, true);
                                    ?>
                                       <div class="timeline-body" style="font-size:11px;color:#888;">
                                          <?= implode(', ', (array)$cf); ?>
                                       </div>
                                    <?php endif; ?>
                                    <div class="timeline-footer">
                                       <a href="<?= $base_url; ?>auditlog/detail/<?= $h->id; ?>" class="btn btn-xs btn-primary">View</a>
                                    </div>
                                 </div>
                              <?php endif; ?>
                           </li>
                        <?php endforeach; ?>
                        </ul>
                     <?php endif; ?>
                  </div>
               </div>

               <!-- Back button -->
               <a href="<?= $base_url; ?>auditlog" class="btn btn-default btn-block">
                  <i class="fa fa-arrow-left"></i> Back to List
               </a>
            </div>

         </div>
      </section>
   </div><!-- /.content-wrapper -->

   <?php include "footer.php"; ?>
   <div class="control-sidebar-bg"></div>
</div><!-- ./wrapper -->

<?php include "comman/code_js_sound.php"; ?>
<?php include "comman/code_js_form.php"; ?>

<script>
var $unchanged = $('.unchanged-row');
var showing    = false;
$('#toggle_unchanged').on('click', function() {
   showing = !showing;
   $unchanged.toggle(showing);
   $(this).html(showing
      ? '<i class="fa fa-eye-slash"></i> Hide Unchanged Fields'
      : '<i class="fa fa-eye"></i> Show Unchanged Fields');
});
</script>

<script>
   $(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");
</script>
</body>
</html>
