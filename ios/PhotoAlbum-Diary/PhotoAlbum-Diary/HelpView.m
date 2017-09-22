//
//  ViewController.m
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "HelpView.h"

@interface HelpView ()
@property BOOL navigationBarHiddenSave;

@end

@implementation HelpView

- (void)viewDidLoad {
    [super viewDidLoad];
    [self.HelpWebView setDelegate:self];
}

-(void) viewWillAppear:(BOOL)animated {
    self.navigationBarHiddenSave = self.navigationController.navigationBarHidden;
    self.navigationController.navigationBarHidden = NO;

    // Do any additional setup after loading the view, typically from a nib.
    NSURL *url = [NSURL URLWithString:MyLib_LogFilePath()];
    NSURLRequest *request = [NSURLRequest requestWithURL:url];
    [self.HelpWebView loadRequest:request];
}

-(void) viewWillDisappear:(BOOL)animated {
    self.navigationController.navigationBarHidden = self.navigationBarHiddenSave;
    [super viewWillDisappear:animated];
}


- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (void)webViewDidFinishLoad:(UIWebView *)webView
{
    CGPoint bottomOffset = CGPointMake(0, self.HelpWebView.scrollView.contentSize.height - self.HelpWebView.scrollView.bounds.size.height);
    [self.HelpWebView.scrollView setContentOffset:bottomOffset animated:YES];
}

@end
