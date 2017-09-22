//
//  ScanerVC.m
//  SuperScanner
//
//  Created by Jeans Huang on 10/19/15.
//  Copyright © 2015 gzhu. All rights reserved.
//

#import "ScanerVC.h"
#import "ScanerView.h"

@import AVFoundation;

@interface ScanerVC ()<AVCaptureMetadataOutputObjectsDelegate,UIAlertViewDelegate>

@property BOOL navigationBarHiddenSave;

//! 加载中视图
@property (weak, nonatomic) IBOutlet UIView     *loadingView;

//! 扫码区域动画视图
@property (weak, nonatomic) IBOutlet ScanerView *scanerView;

//AVFoundation
//! AV协调器
@property (strong,nonatomic) AVCaptureSession           *session;
//! 取景视图
@property (strong,nonatomic) AVCaptureVideoPreviewLayer *previewLayer;

@end

@implementation ScanerVC

- (void)viewDidLoad {
    self.navigationBarHiddenSave = self.navigationController.navigationBarHidden;
    self.navigationController.navigationBarHidden = NO;

    [super viewDidLoad];
    
    self.scanerView.alpha = 0;
    //设置扫描区域边长
    self.scanerView.scanAreaEdgeLength = [[UIScreen mainScreen] bounds].size.width - 2 * 50;
}

-(void) viewWillDisappear:(BOOL)animated {
    self.navigationController.navigationBarHidden = self.navigationBarHiddenSave;
    [super viewWillDisappear:animated];
}

- (void)dealloc{
    [[NSNotificationCenter defaultCenter]removeObserver:self];
}

- (void)viewDidAppear:(BOOL)animated{
    [super viewDidAppear:animated];
    
    if (!self.session){
        
        //添加镜头盖开启动画
        CATransition *animation = [CATransition animation];
        animation.duration = 0.5;
        animation.type = @"cameraIrisHollowOpen";
        animation.timingFunction = UIViewAnimationOptionCurveEaseInOut;
        animation.delegate = self;
        [self.view.layer addAnimation:animation forKey:@"animation"];
        
        //初始化扫码
        [self setupAVFoundation];
        
        //调整摄像头取景区域
        CGRect rect = self.view.bounds;
        rect.origin.y = self.navigationController.navigationBarHidden ? 0 : 64;
        self.previewLayer.frame = rect;
    }
}

//! 动画结束回调
- (void)animationDidStop:(CAAnimation *)theAnimation finished:(BOOL)flag{
    self.loadingView.hidden = YES;
    [UIView animateWithDuration:1.00
                     animations:^{
                         self.scanerView.alpha = 1;
                     }];
}

//! 初始化扫码
- (void)setupAVFoundation{
    //创建会话
    self.session = [[AVCaptureSession alloc] init];
    
    //获取摄像头设备
    AVCaptureDevice *device = [AVCaptureDevice defaultDeviceWithMediaType:AVMediaTypeVideo];
    NSError *error = nil;
    
    //创建输入流
    AVCaptureDeviceInput *input = [AVCaptureDeviceInput deviceInputWithDevice:device error:&error];
    
    if(input) {
        [self.session addInput:input];
    } else {
        //出错处理
        MyDbg(@"%@", error);
        UIAlertController *alertController = [UIAlertController
                                              alertControllerWithTitle:@"Cannot access camera"
                                              message:@"Need camera access. Enable it in setting?"
                                              preferredStyle:UIAlertControllerStyleAlert];
        UIAlertAction *cancelAction = [UIAlertAction
                                       actionWithTitle:NSLocalizedString(@"Cancel", @"Cancel action")
                                       style:UIAlertActionStyleCancel
                                       handler:^(UIAlertAction *action)
                                       {
                                           MyDbg(@"Cancel action");
                                           [self.navigationController popViewControllerAnimated:NO];
                                       }];
        UIAlertAction *okAction = [UIAlertAction
                                   actionWithTitle:NSLocalizedString(@"OK", @"OK action")
                                   style:UIAlertActionStyleDefault
                                   handler:^(UIAlertAction *action)
                                   {
                                       MyDbg(@"OK action");
                                       NSURL *url = [NSURL URLWithString:UIApplicationOpenSettingsURLString];
                                       [[UIApplication sharedApplication] openURL:url];
                                       [self.navigationController popViewControllerAnimated:NO];
                                   }];
        
        [alertController addAction:cancelAction];
        [alertController addAction:okAction];
        [self presentViewController:alertController animated:NO completion:nil];
        return;
    }
    
    //创建输出流
    AVCaptureMetadataOutput *output = [[AVCaptureMetadataOutput alloc] init];
    [self.session addOutput:output];
    
    //设置扫码类型
    output.metadataObjectTypes = @[AVMetadataObjectTypeQRCode,
                                   AVMetadataObjectTypeEAN13Code,//条形码
                                   AVMetadataObjectTypeEAN8Code,
                                   AVMetadataObjectTypeCode128Code];
    //设置代理，在主线程刷新
    [output setMetadataObjectsDelegate:self queue:dispatch_get_main_queue()];
    
    //创建摄像头取景区域
    self.previewLayer = [AVCaptureVideoPreviewLayer layerWithSession:self.session];
    self.previewLayer.videoGravity = AVLayerVideoGravityResizeAspectFill;
    [self.view.layer insertSublayer:self.previewLayer atIndex:0];
    
    if ([self.previewLayer connection].isVideoOrientationSupported)
        [self.previewLayer connection].videoOrientation = AVCaptureVideoOrientationPortrait;
    
    __weak typeof(self) weakSelf = self;
    [[NSNotificationCenter defaultCenter]addObserverForName:AVCaptureInputPortFormatDescriptionDidChangeNotification
                                                     object:nil
                                                      queue:[NSOperationQueue mainQueue]
                                                 usingBlock:^(NSNotification * _Nonnull note) {
                                                     if (weakSelf){
                                                         //调整扫描区域
                                                         AVCaptureMetadataOutput *output = weakSelf.session.outputs.firstObject;
                                                         output.rectOfInterest = [weakSelf.previewLayer metadataOutputRectOfInterestForRect:weakSelf.scanerView.scanAreaRect];
                                                     }
                                                 }];
    
    //开始扫码
    [self.session startRunning];
}

#pragma mark - AVCaptureMetadataOutputObjects Delegate
- (void)captureOutput:(AVCaptureOutput *)captureOutput didOutputMetadataObjects:(NSArray *)metadataObjects fromConnection:(AVCaptureConnection *)connection{
    for (AVMetadataMachineReadableCodeObject *metadata in metadataObjects) {
        if ([metadata.type isEqualToString:AVMetadataObjectTypeQRCode]) {
            if (self.scanResultHandler) {
                NSString *errString;
                self.previewLayer.hidden = YES;
                self.loadingView.hidden = NO;
                [self.session stopRunning];
                [[UIApplication sharedApplication] beginIgnoringInteractionEvents];
                self.scanResultHandler(metadata.stringValue, &errString);
                [[UIApplication sharedApplication] endIgnoringInteractionEvents];

                UIAlertController * alert=   [UIAlertController
                                              alertControllerWithTitle:@""
                                              message:errString
                                              preferredStyle:UIAlertControllerStyleAlert];

                UIAlertAction* ok = [UIAlertAction
                                     actionWithTitle:@"OK"
                                     style:UIAlertActionStyleDefault
                                     handler:^(UIAlertAction * action)
                                     {
                                         [alert dismissViewControllerAnimated:YES completion:nil];
                                         [self.navigationController popViewControllerAnimated:YES];
                     
                                     }];
                [alert addAction:ok];
                [self presentViewController:alert animated:YES completion:nil];

//                [self.navigationController popViewControllerAnimated:YES];
            }else {
                [self.session stopRunning];
                [self.navigationController popViewControllerAnimated:YES];
            }
            
            /*
            UIAlertView *av = [[UIAlertView alloc]initWithTitle:@"二维码"
                                                        message:metadata.stringValue
                                                       delegate:self
                                              cancelButtonTitle:@"OK"
                                              otherButtonTitles: nil];
            [av show];
*/
            break;
        }else{
/*
            [self.session stopRunning];
            UIAlertView *av = [[UIAlertView alloc]initWithTitle:@"条形码"
                                                        message:metadata.stringValue
                                                       delegate:self
                                              cancelButtonTitle:@"OK"
                                              otherButtonTitles: nil];
            [av show];
*/
            break;
        }
    }
}


@end
