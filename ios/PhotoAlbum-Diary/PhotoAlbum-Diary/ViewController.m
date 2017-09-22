//
//  ViewController.m
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "ViewController.h"

@interface ViewController ()

@property BOOL navigationBarHiddenSave;

@end

@implementation ViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    [self.HomeWeb setDelegate:self];
    // Do any additional setup after loading the view, typically from a nib.
}

-(void) viewWillAppear:(BOOL)animated {
    self.navigationBarHiddenSave = self.navigationController.navigationBarHidden;
    self.navigationController.navigationBarHidden = NO;
}

-(void) viewWillDisappear:(BOOL)animated {
    self.navigationController.navigationBarHidden = self.navigationBarHiddenSave;
    [super viewWillDisappear:animated];
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}
- (IBAction)ClickHome:(UIBarButtonItem *)sender {
    MyDbg(@"click home\n");
    NSString *fullURL = @"http://www.baidu.com";
    NSURL *url = [NSURL URLWithString:fullURL];
    NSURLRequest *requestObj = [NSURLRequest requestWithURL:url];
    [self.HomeWeb loadRequest:requestObj];
}

/*
- (IBAction)ClickUpload:(UIBarButtonItem *)sender {
    MyDbg(@"click upload\n");
    NSString *text = @"http://www.baidu.com \n How to add Facebook and Twitter sharing to an iOS app";
    NSURL *url = [NSURL URLWithString:@"https://www.baidu.com/aaa"];
//    UIImage *image = [UIImage imageNamed:@"roadfire-icon-square-200"];
    UIImage *image = [UIImage imageWithData:[NSData dataWithContentsOfURL:[NSURL URLWithString:@"http://ss.bdimg.com/static/superman/img/logo_top_ca79a146.png"]]];
    
    UIActivityViewController *controller = [[UIActivityViewController alloc] initWithActivityItems:@[url] applicationActivities:nil];
    controller.modalInPopover = true;
    controller.restorationIdentifier = @"activity";
    
    
    controller.excludedActivityTypes = @[UIActivityTypePostToWeibo,
                                         UIActivityTypeMessage,
                                         UIActivityTypeMail,
                                         UIActivityTypePrint,
                                         UIActivityTypeCopyToPasteboard,
                                         UIActivityTypeAssignToContact,
                                         UIActivityTypeSaveToCameraRoll,
                                         UIActivityTypeAddToReadingList,
                                         UIActivityTypePostToFlickr,
                                         UIActivityTypePostToVimeo,
                                         UIActivityTypePostToTencentWeibo,
                                         UIActivityTypeAirDrop];
 
    [self presentViewController:controller animated:YES completion:nil];
}
*/

- (IBAction)ClickScan:(UIBarButtonItem *)sender {
/*
    MyDbg(@"click scan\n");
    UIStoryboard *storyboard = [UIStoryboard storyboardWithName:@"Main" bundle:nil];
    ScanerVC *scan = [storyboard instantiateViewControllerWithIdentifier:@"ScanerVC"];
    scan.delegate = self;
    self.navigationController.navigationBarHidden = NO;
    [self.navigationController pushViewController:scan animated:NO];
 */
}
- (IBAction)ClickSetting:(UIBarButtonItem *)sender {
    MyDbg(@"click setting\n");
}


/* monitor webview */
- (BOOL)webView:(UIWebView *)webView shouldStartLoadWithRequest:(NSURLRequest *)request navigationType:(UIWebViewNavigationType)navigationType
{
    NSString *urlStr = [request.URL absoluteString];
    
    // here is your request URL
    NSString *theURL = @"http://xxxxx";
    
    if ([urlStr isEqualToString:theURL]) {
        // do something interesting here.
        return NO;
    }
    
    return YES;
}



#pragma mark - ScannerVC Delegate
-(void)ScannerVCReturn: (BOOL)error result:(NSString *)str
{
    if (error) {

/*
        NSString *msg = [NSString stringWithFormat:@"请在手机【设置】-【隐私】-【相机】选项中，允许【%@】访问您的相机",[[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleName"]];
        
        UIAlertView *av = [[UIAlertView alloc]initWithTitle:@"提醒"
                                                    message:msg
                                                   delegate:self
                                          cancelButtonTitle:@"OK"
                                          otherButtonTitles: nil];
        [av show];
*/
    }else {
        UIAlertController * alert=   [UIAlertController
                                      alertControllerWithTitle:@"二维码"
                                      message:str
                                      preferredStyle:UIAlertControllerStyleAlert];
        
        UIAlertAction* ok = [UIAlertAction
                             actionWithTitle:@"OK"
                             style:UIAlertActionStyleDefault
                             handler:^(UIAlertAction * action)
                             {
                                 [alert dismissViewControllerAnimated:YES completion:nil];
                                 
                             }];
        UIAlertAction* cancel = [UIAlertAction
                                 actionWithTitle:@"Cancel"
                                 style:UIAlertActionStyleDefault
                                 handler:^(UIAlertAction * action)
                                 {
                                     [alert dismissViewControllerAnimated:YES completion:nil];
                                     
                                 }];
        
        [alert addAction:ok];
        [alert addAction:cancel];
        
        [self presentViewController:alert animated:YES completion:nil];
    }
    [self.navigationController popViewControllerAnimated:YES];
}
-(void)ScannerVCExit
{
    self.navigationController.navigationBarHidden = YES;
}

@end
